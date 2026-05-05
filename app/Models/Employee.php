<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Traits\LogsActivity;

class Employee extends Model
{
    use HasFactory, LogsActivity;

    protected $connection = 'mongodb';
    protected $collection = 'employees';

    protected $fillable = [
        'user_id',
        'employee_code',
        'department_id',
        'designation',
        'joining_date',
        'status',
        'skills',
        'experience_years',
        'task_completion_score',
        'points',
        'badges'
    ];

    protected $casts = [
        'points' => 'integer'
    ];

    public function user()        { return $this->belongsTo(User::class); }
    public function department()  { return $this->belongsTo(Department::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function leaves()      { return $this->hasMany(Leave::class); }
    public function payrolls()    { return $this->hasMany(Payroll::class); }
    public function contracts()   { return $this->hasMany(Contract::class); }
    public function alerts()      { return $this->hasMany(Alert::class); }
    public function performanceRecords() { return $this->hasMany(PerformanceRecord::class); }
    public function ratings()     { return $this->hasMany(Rating::class, 'evaluatee_id'); }

    public function getRouteKeyName(): string { return '_id'; }
}
