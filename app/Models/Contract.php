<?php

namespace App\Models;

use App\Traits\SyncTracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Traits\LogsActivity;

class Contract extends Model
{
    use SyncTracking;
    use HasFactory, LogsActivity;

    protected $connection = 'mongodb';
    protected $collection = 'contracts';

    protected $fillable = ['employee_id', 'start_date', 'end_date', 'document_path', 'status', 'basic_salary'];

    public function employee() { return $this->belongsTo(Employee::class); }

    public function getRouteKeyName(): string { return '_id'; }
}
