<?php

namespace App\Models;

use App\Traits\SyncTracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SyncTracking;
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'email_verified_at',
        'password',
        'role',
        'status',
        'otp_code',
        'otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee() { return $this->hasOne(Employee::class); }
    public function complaints() { return $this->hasMany(Complaint::class); }
    public function alerts() { return $this->hasMany(Alert::class); }

    public function isAdmin() { return $this->role === 'admin'; }
    public function isHr() { return $this->role === 'hr'; }
    public function isEmployee() { return $this->role === 'employee'; }

    protected static function booted()
    {
        static::deleting(function ($user) {
            if ($user->employee) {
                $user->employee->delete();
            }
            \App\Models\Complaint::where('user_id', $user->id)->delete();
            \App\Models\Alert::where('user_id', $user->id)->delete();
            \App\Models\Message::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->delete();
            \App\Models\ActivityLog::where('user_id', $user->id)->delete();
            \App\Models\Rating::where('evaluator_id', $user->id)->delete();
        });
    }
}