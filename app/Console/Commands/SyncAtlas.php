<?php

namespace App\Console\Commands;

use App\Models\SyncLog;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\ObjectId;
use Throwable;

class SyncAtlas extends Command
{
    protected $signature = 'mongo:sync-atlas
                            {--dry-run    : Show what WOULD happen without touching any data}
                            {--force      : Overwrite Atlas even when uncertain (requires manual confirmation)}
                            {--collection= : Sync only a specific collection (e.g. --collection=users)}
                            {--limit=0    : Max documents per collection (0 = all)}
                            {--no-backup  : Skip the automatic backup step (not recommended)}';

    protected $description = 'Safely sync local MongoDB to Atlas with conflict detection and full logging';

    // Collections to NEVER sync (local-only data)
    private const EXCLUDED_COLLECTIONS = ['sync_logs'];

    // Summary counters
    private int $inserted  = 0;
    private int $updated   = 0;
    private int $skipped   = 0;
    private int $conflicts = 0;
    private int $failed    = 0;

    private string $sessionId;
    private bool $dryRun;
    private bool $force;

    public function handle(): int
    {
        $this->sessionId = 'sync_' . now()->format('Ymd_His') . '_' . Str::random(6);
        $this->dryRun    = (bool) $this->option('dry-run');
        $this->force     = (bool) $this->option('force');

        $this->printHeader();

        // ── Safety: require manual confirmation for --force ──────────────
        if ($this->force && ! $this->dryRun) {
            $this->warn("⚠  --force mode will overwrite Atlas records where local is newer.");
            if (! $this->confirm('Are you sure you want to proceed with --force?')) {
                $this->info("Cancelled.");
                return self::SUCCESS;
            }
        }

        // ── Step 1: Backup (unless skipped) ─────────────────────────────
        if (! $this->option('no-backup') && ! $this->dryRun) {
            $this->info("📦 Running backup before sync...");
            $result = $this->call('mongo:backup');
            if ($result !== self::SUCCESS) {
                $this->error("❌ Backup failed. Aborting sync to keep data safe.");
                $this->error("   Fix the backup issue or run with --no-backup (not recommended).");
                return self::FAILURE;
            }
            $this->newLine();
        }

        // ── Step 2: Connect to BOTH databases ───────────────────────────
        try {
            $localUri      = env('MONGODB_URI', 'mongodb://127.0.0.1:27017');
            $localDb       = env('MONGODB_DATABASE', 'labour_compliance');
            $atlasUri      = env('MONGODB_URI_ATLAS');
            $atlasDb       = env('MONGODB_DATABASE_ATLAS', 'labour_compliance');

            if (empty($atlasUri)) {
                $this->error("❌ MONGODB_URI_ATLAS is not set in .env. Cannot connect to Atlas.");
                return self::FAILURE;
            }

            $this->info("🔌 Connecting to local MongoDB...");
            $localClient  = new MongoClient($localUri);
            $localMongo   = $localClient->selectDatabase($localDb);

            $this->info("🔌 Connecting to MongoDB Atlas...");
            $atlasClient  = new MongoClient($atlasUri, ['serverSelectionTimeoutMS' => 10000]);
            $atlasMongo   = $atlasClient->selectDatabase($atlasDb);
            // Ping to confirm
            $atlasMongo->command(['ping' => 1]);
            $this->info("   ✓ Atlas connected.");
            $this->newLine();

        } catch (Throwable $e) {
            $this->error("❌ Connection failed: {$e->getMessage()}");
            $this->error("   Check your internet / Atlas URI and try again.");
            return self::FAILURE;
        }

        // ── Step 3: Determine which collections to sync ──────────────────
        if ($this->option('collection')) {
            $collections = [$this->option('collection')];
        } else {
            $cols        = iterator_to_array($localMongo->listCollections());
            $collections = array_filter(
                array_map(fn($c) => $c->getName(), $cols),
                fn($name) => ! in_array($name, self::EXCLUDED_COLLECTIONS)
            );
        }

        // ── Step 4: Process each collection ─────────────────────────────
        foreach ($collections as $colName) {
            $this->syncCollection($colName, $localMongo, $atlasMongo);
        }

        // ── Step 5: Summary ──────────────────────────────────────────────
        $this->printSummary();

        return ($this->failed > 0 || $this->conflicts > 0)
            ? self::FAILURE
            : self::SUCCESS;
    }

    // =========================================================================
    // CORE SYNC LOGIC PER COLLECTION
    // =========================================================================

    private function syncCollection(string $colName, $localMongo, $atlasMongo): void
    {
        $this->line("<options=bold>📂 Collection: {$colName}</>");

        $localCol  = $localMongo->selectCollection($colName);
        $atlasCol  = $atlasMongo->selectCollection($colName);

        // Only sync pending documents (or all if force)
        $filter    = $this->force ? [] : ['$or' => [
            ['sync_status' => 'pending'],
            ['sync_status' => 'failed'],
            ['sync_status' => ['$exists' => false]],
        ]];

        $limit     = (int) $this->option('limit');
        $options   = $limit > 0 ? ['limit' => $limit] : [];

        try {
            $cursor    = $localCol->find($filter, $options);
            $docCount  = 0;

            foreach ($cursor as $localDoc) {
                $docCount++;
                $this->processDocument($localDoc, $localCol, $atlasCol, $colName);
            }

            if ($docCount === 0) {
                $this->line("  <fg=gray>  ~ No pending documents.</>");
            }

        } catch (Throwable $e) {
            $this->error("  ❌ Collection error: {$e->getMessage()}");
            $this->failed++;
        }

        $this->newLine();
    }

    // =========================================================================
    // PROCESS A SINGLE DOCUMENT
    // =========================================================================

    private function processDocument($localDoc, $localCol, $atlasCol, string $colName): void
    {
        $startMs   = microtime(true);
        $docId     = (string) $localDoc['_id'];
        $localAt   = $localDoc['local_updated_at'] ?? null;
        $localVer  = (int) ($localDoc['sync_version'] ?? 0);

        try {
            // Look up in Atlas by _id
            $atlasDoc  = $atlasCol->findOne(['_id' => $localDoc['_id']]);

            // ── CASE 1: Not in Atlas → safe INSERT ───────────────────────
            if ($atlasDoc === null) {
                if (! $this->dryRun) {
                    $atlasCol->insertOne((array) $localDoc);
                    $this->markLocalSynced($localCol, $localDoc['_id'], now()->toISOString());
                }
                $this->logAndPrint($colName, $docId, 'insert',
                    "New local doc — inserted into Atlas", $localAt, null, $localVer, $startMs);
                $this->inserted++;
                return;
            }

            $atlasAt      = isset($atlasDoc['local_updated_at'])
                ? (string) $atlasDoc['local_updated_at']
                : (isset($atlasDoc['updated_at']) ? (string) $atlasDoc['updated_at'] : null);
            $lastSyncedAt = isset($localDoc['atlas_updated_at'])
                ? (string) $localDoc['atlas_updated_at']
                : null;

            $localNewer   = $localAt && $atlasAt && ($localAt > $atlasAt);
            $atlasChanged = $atlasAt && $lastSyncedAt && ($atlasAt !== $lastSyncedAt);
            $localChanged = ($localDoc['sync_status'] ?? 'pending') === 'pending';

            // ── CASE 2: CONFLICT — both sides changed ─────────────────────
            if ($localChanged && $atlasChanged && ! $this->force) {
                if (! $this->dryRun) {
                    $localCol->updateOne(
                        ['_id' => $localDoc['_id']],
                        ['$set' => [
                            'sync_status'    => 'conflict',
                            'last_sync_error' => "Both local and Atlas changed after last sync. Atlas: {$atlasAt}, Local: {$localAt}",
                        ]]
                    );
                }
                $this->logAndPrint($colName, $docId, 'conflict',
                    "Both sides changed — manual review needed", $localAt, $atlasAt, $localVer, $startMs);
                $this->conflicts++;
                return;
            }

            // ── CASE 3: Atlas is newer → SKIP local ──────────────────────
            if ($atlasAt && $localAt && ($atlasAt > $localAt) && ! $this->force) {
                $this->logAndPrint($colName, $docId, 'skip',
                    "Atlas is newer ({$atlasAt} > {$localAt})", $localAt, $atlasAt, $localVer, $startMs);
                $this->skipped++;
                return;
            }

            // ── CASE 4: Local is newer (or force) → UPDATE Atlas ──────────
            if ($localNewer || $this->force || ! $atlasAt) {
                if (! $this->dryRun) {
                    // Build update — exclude _id from $set
                    $updateData = (array) $localDoc;
                    unset($updateData['_id']);
                    $updateData['atlas_updated_at'] = $localAt;

                    $atlasCol->updateOne(
                        ['_id' => $localDoc['_id']],
                        ['$set' => $updateData],
                        ['upsert' => false]   // NEVER upsert here — only insert via Case 1
                    );
                    $this->markLocalSynced($localCol, $localDoc['_id'], $localAt ?? now()->toISOString());
                }
                $this->logAndPrint($colName, $docId, 'update',
                    "Local newer — Atlas updated", $localAt, $atlasAt, $localVer, $startMs);
                $this->updated++;
                return;
            }

            // ── CASE 5: Nothing to do ────────────────────────────────────
            $this->logAndPrint($colName, $docId, 'skip',
                "No change detected", $localAt, $atlasAt, $localVer, $startMs);
            $this->skipped++;

        } catch (Throwable $e) {
            if (! $this->dryRun) {
                $localCol->updateOne(
                    ['_id' => $localDoc['_id']],
                    ['$set' => ['sync_status' => 'failed', 'last_sync_error' => $e->getMessage()]]
                );
            }
            $this->logAndPrint($colName, $docId, 'failed',
                $e->getMessage(), $localAt, null, $localVer, $startMs);
            $this->failed++;
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function markLocalSynced($localCol, $id, string $atlasAt): void
    {
        $localCol->updateOne(
            ['_id' => $id],
            ['$set' => [
                'sync_status'      => 'synced',
                'synced_at'        => now()->toISOString(),
                'atlas_updated_at' => $atlasAt,
                'last_sync_error'  => null,
            ]]
        );
    }

    private function logAndPrint(
        string $col, string $id, string $action,
        string $reason, ?string $localAt, ?string $atlasAt,
        int $ver, float $startMs
    ): void {
        $durationMs = round((microtime(true) - $startMs) * 1000, 2);

        $icon = match($action) {
            'insert'   => '<fg=green>  ✓ INSERT  </>',
            'update'   => '<fg=blue>  ✓ UPDATE  </>',
            'skip'     => '<fg=gray>  ~ SKIP    </>',
            'conflict' => '<fg=yellow>  ⚡ CONFLICT</>',
            'failed'   => '<fg=red>  ✗ FAILED  </>',
            default    => "  ? {$action}  ",
        };

        $dryTag = $this->dryRun ? '<fg=cyan>[DRY]</> ' : '';
        $this->line("  {$dryTag}{$icon} {$id}  <fg=gray>{$reason}</>");

        // Persist to sync_logs (even in dry-run, to record intent)
        try {
            SyncLog::record(
                sessionId: $this->sessionId,
                collection: $col,
                documentId: $id,
                action: $action,
                reason: $reason,
                meta: [
                    'local_version'    => $ver,
                    'local_updated_at' => $localAt,
                    'atlas_updated_at' => $atlasAt,
                    'duration_ms'      => $durationMs,
                ],
                dryRun: $this->dryRun,
            );
        } catch (Throwable) {
            // Don't let log failures break the sync
        }
    }

    private function printHeader(): void
    {
        $mode = $this->dryRun ? ' [DRY-RUN — no data will be changed]' : '';
        $this->newLine();
        $this->info("╔══════════════════════════════════════════╗");
        $this->info("║   MongoDB Atlas Sync — Session {$this->sessionId}");
        $this->info("║   Mode:{$mode}");
        $this->info("╚══════════════════════════════════════════╝");
        $this->newLine();
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info("╔══════════════════ SYNC SUMMARY ══════════════════╗");
        $this->info("  Session  : {$this->sessionId}");
        $this->info("  Inserted : {$this->inserted}");
        $this->info("  Updated  : {$this->updated}");
        $this->info("  Skipped  : {$this->skipped}");
        $this->info("  Conflicts: {$this->conflicts}" . ($this->conflicts > 0 ? '  ← review in sync_logs' : ''));
        $this->info("  Failed   : {$this->failed}" . ($this->failed > 0 ? '  ← check last_sync_error field' : ''));
        $this->info("╚══════════════════════════════════════════════════╝");
        $this->newLine();

        if ($this->dryRun) {
            $this->warn("ℹ  This was a DRY-RUN. No data was changed.");
            $this->info("   Run without --dry-run to apply these changes.");
        }
    }
}
