<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MongoDB\Client as MongoClient;

/**
 * Finds and fixes records with future-dated created_at/updated_at
 * across all collections. Resets them to a realistic past date.
 */
class FixFutureDates extends Command
{
    protected $signature   = 'hr:fix-future-dates {--dry-run : Show what would be fixed without changing anything}';
    protected $description = 'Fix records that have created_at/updated_at dates in the future';

    private const COLLECTIONS = [
        'complaints', 'leaves', 'tasks', 'attendances',
        'payrolls', 'alerts', 'messages', 'ratings',
        'performance_records', 'activity_logs', 'monthly_rewards',
    ];

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $today   = Carbon::today();
        $fixed   = 0;

        $uri    = env('MONGODB_URI', 'mongodb://127.0.0.1:27017');
        $dbName = env('MONGODB_DATABASE', 'labour_compliance');

        $client = new MongoClient($uri);
        $db     = $client->selectDatabase($dbName);

        $this->info('');
        $this->info('🔍 Scanning for future-dated records' . ($dryRun ? ' [DRY-RUN]' : '') . '...');
        $this->info('   Today is: ' . $today->toDateString());
        $this->newLine();

        foreach (self::COLLECTIONS as $colName) {
            $col  = $db->selectCollection($colName);
            $docs = $col->find([]);
            $colFixed = 0;

            foreach ($docs as $doc) {
                $needsFix   = false;
                $updateData = [];

                // Check created_at
                foreach (['created_at', 'updated_at'] as $field) {
                    if (isset($doc[$field])) {
                        try {
                            $date = Carbon::parse((string) $doc[$field]);
                            if ($date->isFuture()) {
                                // Reset to a random date in the last 60 days, but not today
                                $fixedDate         = $today->copy()->subDays(rand(1, 60));
                                $updateData[$field] = $fixedDate->toISOString();
                                $needsFix           = true;
                            }
                        } catch (\Throwable) {
                            // unparseable date — skip
                        }
                    }
                }

                if ($needsFix) {
                    $id = (string) $doc['_id'];
                    $this->line("  <fg=yellow>  FIX</> [{$colName}] {$id}");

                    foreach ($updateData as $field => $val) {
                        $this->line("       {$field}: future → {$val}");
                    }

                    if (! $dryRun) {
                        $col->updateOne(
                            ['_id' => $doc['_id']],
                            ['$set' => $updateData]
                        );
                    }

                    $colFixed++;
                    $fixed++;
                }
            }

            if ($colFixed === 0) {
                $this->line("  <fg=green>  ✓ {$colName}</> — no future dates.");
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->warn("DRY-RUN: {$fixed} records would be fixed. Run without --dry-run to apply.");
        } else {
            $this->info("✅ Fixed {$fixed} records with future dates.");
        }

        return self::SUCCESS;
    }
}
