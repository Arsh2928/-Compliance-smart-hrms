<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'payrolls';

    protected $fillable = [
        'employee_id', 'month', 'year',
        'basic_salary', 'overtime_hours', 'overtime_pay',
        'deductions', 'net_salary', 'status',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }

    public function getRouteKeyName(): string { return '_id'; }
}
