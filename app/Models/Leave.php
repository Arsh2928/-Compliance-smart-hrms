<?php

namespace App\Models;

use App\Traits\SyncTracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Traits\LogsActivity;

class Leave extends Model
{
    use SyncTracking;
    use HasFactory, LogsActivity;

    protected $connection = 'mongodb';
    protected $collection = 'leaves';

    protected $fillable = ['employee_id', 'start_date', 'end_date', 'type', 'reason', 'status', 'admin_remark'];

    public function employee() { return $this->belongsTo(Employee::class); }

    public function getRouteKeyName(): string { return '_id'; }
}