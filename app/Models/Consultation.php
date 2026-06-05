<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'clinic_id',
        'chief_complaint',
        'notes',
        'consultation_date',
        'blood_pressure',
        'heart_rate',
        'respiratory_rate',
        'temperature',
        'weight',
        'height',
        'oxygen_saturation',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'datetime',
        ];
    }

    // The doctor who conducted this consultation
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // The patient this consultation belongs to
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    // The clinic where this consultation took place
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    // The diseases diagnosed during this consultation
    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'consultation_diseases')
                    ->withPivot('type', 'status', 'symptoms', 'disease_name_snapshot')
                    ->withTimestamps();
    }

    // The prescriptions issued during this consultation
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'consultation_id');
    }
}