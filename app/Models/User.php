<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'role',
        'password',
        'prc_id',
        'specialization',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Inside app/Models/User.php
    public function clinics()
    {
        // A user can belong to many clinics.
        return $this->belongsToMany(Clinic::class, 'clinic_user', 'user_id', 'clinic_id')->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }
}