<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationDisease extends Model
{
    use HasFactory;

    protected $table = 'consultation_diseases';

    protected $fillable = [
        'consultation_id',
        'disease_id',
        'disease_name_snapshot',
        'symptoms',
        'status',
        'type',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    public function disease()
    {
        return $this->belongsTo(Disease::class, 'disease_id')->withTrashed();
    }
}