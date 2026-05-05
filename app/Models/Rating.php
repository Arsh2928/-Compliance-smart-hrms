<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'ratings';

    protected $fillable = [
        'employee_id',
        'evaluator_id',
        'month',
        'categories', // Array: work_quality, punctuality, teamwork, task_completion, discipline
        'average_rating',
        'feedback'
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function evaluator() { return $this->belongsTo(User::class, 'evaluator_id'); }
}
