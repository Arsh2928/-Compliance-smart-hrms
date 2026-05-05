<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'attendances';

    protected $fillable = ['employee_id', 'date', 'check_in', 'check_out', 'total_hours', 'status'];

    public function employee() { return $this->belongsTo(Employee::class); }
}