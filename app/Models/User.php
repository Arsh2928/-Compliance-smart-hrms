<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee() { return $this->hasOne(Employee::class); }
    public function complaints() { return $this->hasMany(Complaint::class); }
    public function alerts() { return $this->hasMany(Alert::class); }

    public function isAdmin() { return $this->role === 'admin'; }
    public function isHr() { return $this->role === 'hr'; }
    public function isEmployee() { return $this->role === 'employee'; }
}