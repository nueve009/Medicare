<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disease extends Model
{
    use HasFactory, SoftDeletes;

    // Explicitly tell Laravel the primary key is disease_id
    protected $primaryKey = 'disease_id';

    protected $fillable = [
        'disease_name',
        'description',
        'symptoms',
    ];
    
    // (Optional) If you want to easily pull all consultations related to a disease:
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'disease_id', 'disease_id');
    }
}