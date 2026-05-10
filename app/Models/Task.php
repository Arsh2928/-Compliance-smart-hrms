<?php

namespace App\Models;

use App\Traits\SyncTracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Task extends Model
{
    use SyncTracking;
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'tasks';

    protected $fillable = [
        'employee_id',
        'assigned_by',
        'title',
        'description',
        'status',
        'deadline',
        'completed_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
