<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'disease_id',
        'consultation_date',
        'symptoms',
        'diagnosis_notes',
        'status'
    ];

    // The patient being treated
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    // The doctor (user) handling the consultation
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    // The diagnosed disease
    public function disease()
    {
        return $this->belongsTo(Disease::class, 'disease_id', 'disease_id');
    }
}