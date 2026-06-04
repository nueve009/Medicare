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
        'type',
    ];
}