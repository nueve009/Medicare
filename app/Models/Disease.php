<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disease extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'created_by',
        'disease_name',
        'description',
        'symptoms',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function consultations()
    {
        return $this->belongsToMany(Consultation::class, 'consultation_diseases')
                    ->withPivot('type', 'status', 'symptoms', 'disease_name_snapshot')
                    ->withTimestamps();
    }

    public function patients()
    {
        return $this->hasManyThrough(
            Patient::class,
            Consultation::class,
            'id',
            'id',
            'id',
            'patient_id'
        );
    }
}