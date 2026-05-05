<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'contracts';

    protected $fillable = ['employee_id', 'start_date', 'end_date', 'document_path', 'status'];

    public function employee() { return $this->belongsTo(Employee::class); }

    public function getRouteKeyName(): string { return '_id'; }
}
