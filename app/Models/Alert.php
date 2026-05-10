<?php

namespace App\Models;

use App\Traits\SyncTracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Alert extends Model
{
    use SyncTracking;
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'alerts';

    protected $fillable = ['user_id', 'type', 'message', 'is_read', 'link'];

    public function user() { return $this->belongsTo(User::class); }

    public function getRouteKeyName(): string { return '_id'; }
}
