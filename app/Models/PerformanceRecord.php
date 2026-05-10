<?php

namespace App\Models;

use App\Traits\SyncTracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PerformanceRecord extends Model
{
    use SyncTracking;
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'performance_records';

    protected $fillable = [
        // Identity
        'employee_id',
        'month',

        // Scores
        'live_score',            // Mid-month projected score
        'final_score',           // Frozen snapshot at evaluation time

        // Components (normalised 0-1)
        'attendance_component',
        'rating_component',
        'task_component',
        'consistency_component',

        // Raw inputs stored for transparency
        'average_rating',        // After outlier removal
        'streak_days',

        // Ranking
        'rank',
        'rank_delta',            // positive = improved vs prior month
        'percentile',
        'reward_tier',

        // Anti-cheat audit
        'flags',                 // Array of warning strings
    ];

    protected $casts = [
        'flags'      => 'array',
        'rank_delta' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
