<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['clinic_name', 'doctor_id', 'address', 'phone_number'];

    // The users (doctors/assistants) that work at this clinic
    public function users()
    {
        return $this->belongsToMany(User::class, 'clinic_user', 'clinic_id', 'user_id')->withTimestamps();
    }

    // The patients registered at this clinic
    public function patients()
    {
        return $this->hasMany(Patient::class, 'clinic_id',);
    }

    // The consultations that happened at this clinic
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'clinic_id');
    }
}