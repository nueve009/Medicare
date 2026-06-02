<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disease extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'disease_name',
        'description',
        'symptoms',
    ];
    
    public function consultations()
    {
        return $this->belongsToMany(Consultation::class, 'consultation_diseases')
                    ->withPivot('type')
                    ->withTimestamps();
    }
}