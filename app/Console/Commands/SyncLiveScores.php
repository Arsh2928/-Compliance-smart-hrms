<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\PerformanceRecord;
use App\Services\ScoringService;
use Illuminate\Support\Facades\Cache;

class SyncLiveScores extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hr:sync-live-scores';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize dynamic live scores for the current month into the database so leaderboards are accurate.';

    /**
     * Execute the console command.
     */
    public function handle(ScoringService $scoringService)
    {
        try {
            $currentMonth = now()->format('Y-m');
            $employees = Employee::all();

            $count = 0;
            foreach ($employees as $employee) {
                $result = $scoringService->computeScore($employee, $currentMonth);

                $record = PerformanceRecord::firstOrNew([
                    'employee_id' => $employee->id,
                    'month'       => $currentMonth
                ]);

                $record->live_score = $result['live_score'];
                $record->components = $result['components'];
                $record->flags      = $result['flags'];

                // Do not overwrite final_score if it exists (though it shouldn't for ongoing month)
                $record->save();
                $count++;
            }

            // Clear leaderboard caches so it reflects new data instantly
            Cache::flush();

            $this->info("Successfully synced live scores for {$count} employees.");
        } catch (\Exception $e) {
            $this->error('MongoDB connection failed — could not sync live scores: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('[SyncLiveScores] MongoDB error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
