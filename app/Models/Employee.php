<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'employees';

    protected $fillable = [
        'user_id', 'department_id', 'employee_code', 'phone', 'address', 'joined_date',
        'attendance_points', 'total_points', 'rating', 'performance_score', 'badges'
    ];

    public function user()        { return $this->belongsTo(User::class); }
    public function department()  { return $this->belongsTo(Department::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function leaves()      { return $this->hasMany(Leave::class); }
    public function payrolls()    { return $this->hasMany(Payroll::class); }
    public function contracts()   { return $this->hasMany(Contract::class); }
    public function alerts()      { return $this->hasMany(Alert::class); }

    public function getRouteKeyName(): string { return '_id'; }
}
