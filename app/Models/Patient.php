<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes; // Enable soft deletes

    protected $fillable = [
        'created_by',
        'clinic_id',
        'first_name',
        'last_name',
        'gender',
        'birthdate',
        'email',
        'phone_number',
        'address',
        'blood_type',
        'civil_status',
        'height',
        'weight',
        'temp',
        'bp',
        'allergies',
    ];

    // Define the relationship to the User who created the patient
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

        public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id');
    }
}