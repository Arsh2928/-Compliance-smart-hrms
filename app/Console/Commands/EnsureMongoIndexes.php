<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class EnsureMongoIndexes extends Command
{
    protected $signature = 'db:ensure-indexes';
    protected $description = 'Ensure MongoDB performance indexes are created for massive scale.';

    public function handle()
    {
        $this->info('Creating MongoDB compound indexes for performance...');

        try {
            // Performance Records (Leaderboard query optimization)
            Schema::connection('mongodb')->table('performance_records', function ($collection) {
                $collection->index(['month' => -1, 'final_score' => -1]);
                $collection->index(['employee_id' => 1, 'month' => -1]);
            });
            $this->info('✔ Performance Records indexes created.');

            // Attendances (Dashboard & Scoring Service optimization)
            Schema::connection('mongodb')->table('attendances', function ($collection) {
                $collection->index(['employee_id' => 1, 'date' => -1]);
                $collection->index(['date' => -1]);
            });
            $this->info('✔ Attendances indexes created.');

            // Leaves (Conflict checking optimization)
            Schema::connection('mongodb')->table('leaves', function ($collection) {
                $collection->index(['employee_id' => 1, 'status' => 1]);
                $collection->index(['start_date' => 1, 'end_date' => 1]);
            });
            $this->info('✔ Leaves indexes created.');

            // Ratings (Scoring Service outlier checking)
            Schema::connection('mongodb')->table('ratings', function ($collection) {
                $collection->index(['employee_id' => 1, 'month' => -1]);
                $collection->index(['evaluator_id' => 1, 'month' => -1]);
            });
            $this->info('✔ Ratings indexes created.');

            // Contracts (Checking for expiry)
            Schema::connection('mongodb')->table('contracts', function ($collection) {
                $collection->index(['status' => 1, 'end_date' => 1]);
            });
            $this->info('✔ Contracts indexes created.');

            $this->info('✅ All MongoDB indexes successfully established!');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create indexes: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
