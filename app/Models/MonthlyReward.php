<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class MonthlyReward extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'monthly_rewards';

    protected $fillable = [
        'employee_id',
        'month',
        'rank',
        'percentile',
        'reward_tier', // Gold, Silver, Bronze, None
        'bonus_points_awarded'
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}
