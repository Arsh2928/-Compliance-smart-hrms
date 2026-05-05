<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Traits\LogsActivity;

class Complaint extends Model
{
    use HasFactory, LogsActivity;

    protected $connection = 'mongodb';
    protected $collection = 'complaints';

    protected $fillable = ['user_id', 'title', 'description', 'is_anonymous', 'status', 'admin_response'];

    public function user() { return $this->belongsTo(User::class); }

    public function getRouteKeyName(): string { return '_id'; }
}