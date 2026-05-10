<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MongoDB\Client as MongoClient;

/**
 * Pull data FROM Atlas TO local MongoDB.
 *
 * This should be run ONCE when setting up the offline-first workflow,
 * or anytime you want to refresh local with the latest Atlas data.
 *
 * SAFE: Never touches Atlas. Only writes to local.
 */
class PullFromAtlas extends Command
{
    protected $signature = 'mongo:pull-atlas
                            {--collection= : Pull only a specific collection}
                            {--fresh       : Drop local collection before pulling (full replace)}
                            {--dry-run     : Show what would be pulled without writing anything}';

    protected $description = 'Pull data from MongoDB Atlas into local MongoDB (safe: Atlas is never touched)';

    private const EXCLUDED_COLLECTIONS = ['sync_logs'];

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $fresh   = (bool) $this->option('fresh');

        $this->newLine();
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   MongoDB Atlas → Local Pull');
        if ($dryRun) {
            $this->info('║   Mode: DRY-RUN — nothing will be written');
        }
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        // ── Validate Atlas URI ──────────────────────────────────────────
        $atlasUri = env('MONGODB_URI_ATLAS');
        if (empty($atlasUri)) {
            $this->error('❌ MONGODB_URI_ATLAS is not set in .env');
            return self::FAILURE;
        }

        // ── Safety warning for --fresh ──────────────────────────────────
        if ($fresh && ! $dryRun) {
            $this->warn('⚠  --fresh will DROP and replace each local collection with Atlas data.');
            if (! $this->confirm('Are you sure? Local-only data will be lost.')) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }
        }

        // ── Connect ─────────────────────────────────────────────────────
        try {
            $localUri  = env('MONGODB_URI', 'mongodb://127.0.0.1:27017');
            $localDb   = env('MONGODB_DATABASE', 'labour_compliance');
            $atlasDb   = env('MONGODB_DATABASE_ATLAS', 'labour_compliance');

            $this->info('🔌 Connecting to Atlas...');
            $atlasClient = new MongoClient($atlasUri, ['serverSelectionTimeoutMS' => 10000]);
            $atlasMongo  = $atlasClient->selectDatabase($atlasDb);
            $atlasMongo->command(['ping' => 1]);
            $this->info('   ✓ Atlas connected.');

            $this->info('🔌 Connecting to local MongoDB...');
            $localClient = new MongoClient($localUri);
            $localMongo  = $localClient->selectDatabase($localDb);
            $this->info('   ✓ Local connected.');
            $this->newLine();

        } catch (\Throwable $e) {
            $this->error('❌ Connection failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // ── Determine collections ────────────────────────────────────────
        if ($this->option('collection')) {
            $collections = [$this->option('collection')];
        } else {
            $cols = iterator_to_array($atlasMongo->listCollections());
            $collections = array_filter(
                array_map(fn($c) => $c->getName(), $cols),
                fn($name) => ! in_array($name, self::EXCLUDED_COLLECTIONS)
            );
        }

        $totalInserted = 0;
        $totalSkipped  = 0;
        $failed        = [];

        // ── Pull each collection ─────────────────────────────────────────
        foreach ($collections as $colName) {
            $this->line("<options=bold>📂 {$colName}</>");

            try {
                $atlasCol = $atlasMongo->selectCollection($colName);
                $localCol = $localMongo->selectCollection($colName);

                $atlasDocs = iterator_to_array($atlasCol->find([]));
                $count     = count($atlasDocs);

                if ($count === 0) {
                    $this->line("  <fg=gray>  ~ Empty on Atlas, skipping.</>");
                    continue;
                }

                if ($dryRun) {
                    $this->line("  <fg=cyan>  [DRY] Would pull {$count} documents from Atlas.</>");
                    $totalInserted += $count;
                    continue;
                }

                if ($fresh) {
                    // Drop local collection entirely and re-insert all Atlas docs
                    $localCol->drop();
                    $localCol->insertMany($atlasDocs);
                    $this->line("  <fg=green>  ✓ Replaced</> with {$count} docs from Atlas (--fresh).");
                    $totalInserted += $count;
                } else {
                    // Merge: insert Atlas docs that don't exist locally (by _id)
                    $inserted = 0;
                    $skipped  = 0;
                    foreach ($atlasDocs as $atlasDoc) {
                        $exists = $localCol->findOne(['_id' => $atlasDoc['_id']]);
                        if ($exists === null) {
                            // New doc from Atlas — set sync metadata
                            $doc = (array) $atlasDoc;
                            $doc['sync_status']      = 'synced';
                            $doc['atlas_updated_at'] = $atlasDoc['updated_at']
                                ?? $atlasDoc['local_updated_at']
                                ?? now()->toISOString();
                            $doc['synced_at']        = now()->toISOString();
                            $doc['sync_version']     = 1;
                            $doc['last_sync_error']  = null;
                            $localCol->insertOne($doc);
                            $inserted++;
                        } else {
                            $skipped++;
                        }
                    }
                    $totalInserted += $inserted;
                    $totalSkipped  += $skipped;
                    $this->line("  <fg=green>  ✓ Inserted {$inserted}</> new, <fg=gray>skipped {$skipped}</> already-local.");
                }

            } catch (\Throwable $e) {
                $failed[] = $colName;
                $this->line("  <fg=red>  ✗ Failed: {$e->getMessage()}</>");
            }
        }

        // ── Summary ──────────────────────────────────────────────────────
        $this->newLine();
        $this->info('╔══════════════════ PULL SUMMARY ══════════════════╗');
        $this->info("  Pulled   : {$totalInserted} documents");
        $this->info("  Skipped  : {$totalSkipped} (already existed locally)");
        if (! empty($failed)) {
            $this->warn('  Failed   : ' . implode(', ', $failed));
        }
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->newLine();

        if ($dryRun) {
            $this->warn('ℹ  DRY-RUN — nothing was written. Run without --dry-run to apply.');
        } else {
            $this->info('✅ Local MongoDB now mirrors Atlas. Your app is ready to work offline.');
        }

        return empty($failed) ? self::SUCCESS : self::FAILURE;
    }
}
