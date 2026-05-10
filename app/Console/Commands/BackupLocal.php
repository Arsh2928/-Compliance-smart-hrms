<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MongoDB\Client as MongoClient;

class BackupLocal extends Command
{
    protected $signature = 'mongo:backup
                            {--path= : Custom backup path (default: storage/mongo-backups/)}';

    protected $description = 'Export all local MongoDB collections to JSON backup files';

    public function handle(): int
    {
        $timestamp   = now()->format('Y-m-d_H-i-s');
        $backupRoot  = storage_path('mongo-backups');
        $backupPath  = $this->option('path') ?? "{$backupRoot}/{$timestamp}";

        if (! is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $this->info("📦 Starting local MongoDB backup...");
        $this->info("   Backup path: {$backupPath}");
        $this->newLine();

        try {
            $uri    = env('MONGODB_URI', 'mongodb://127.0.0.1:27017');
            $dbName = env('MONGODB_DATABASE', 'labour_compliance');

            $client = new MongoClient($uri);
            $db     = $client->selectDatabase($dbName);

            $collections = iterator_to_array($db->listCollections());

            if (empty($collections)) {
                $this->warn("⚠  No collections found in '{$dbName}'. Is local MongoDB populated?");
                return self::SUCCESS;
            }

            $totalDocs    = 0;
            $filesSaved   = 0;
            $failedCols   = [];

            foreach ($collections as $colInfo) {
                $colName = $colInfo->getName();

                // Never back up sync_logs (they're huge and local-only)
                if ($colName === 'sync_logs') {
                    $this->line("  <fg=gray>  SKIP  {$colName} (sync_logs excluded)</>");
                    continue;
                }

                try {
                    $col      = $db->selectCollection($colName);
                    $cursor   = $col->find([]);
                    $docs     = [];

                    foreach ($cursor as $doc) {
                        // Convert BSON to JSON-safe array
                        $docs[] = json_decode(json_encode($doc), true);
                    }

                    $count    = count($docs);
                    $json     = json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $file     = "{$backupPath}/{$colName}.json";

                    file_put_contents($file, $json);

                    $totalDocs  += $count;
                    $filesSaved++;

                    $this->line("  <fg=green>  ✓</> {$colName} ({$count} docs) → " . basename($file));

                } catch (\Throwable $e) {
                    $failedCols[] = $colName;
                    $this->line("  <fg=red>  ✗</> {$colName}: {$e->getMessage()}");
                }
            }

            // Write a manifest file
            $manifest = [
                'created_at'   => now()->toISOString(),
                'database'     => $dbName,
                'uri'          => 'local',
                'total_docs'   => $totalDocs,
                'collections'  => $filesSaved,
                'failed'       => $failedCols,
            ];
            file_put_contents("{$backupPath}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

            $this->newLine();
            $this->info("✅ Backup complete: {$filesSaved} collections, {$totalDocs} documents");
            $this->info("   Location: {$backupPath}");

            if (! empty($failedCols)) {
                $this->warn("⚠  Failed collections: " . implode(', ', $failedCols));
                return self::FAILURE;
            }

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("❌ Backup FAILED: {$e->getMessage()}");
            $this->error("   Cannot proceed with sync until backup succeeds.");
            return self::FAILURE;
        }
    }
}
