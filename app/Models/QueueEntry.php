<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueEntry extends Model
{
    protected $fillable = [
        'patient_id',
        'clinic_id',
        'added_by',
        'queued_at',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}