<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consultation_id',
        'generic_id',
        'generic_name_snapshot', // Added
        'brand_id',
        'brand_name_snapshot',
        'dosage',
        'frequency',
        'duration',
        'instructions',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    public function generic()
    {
        return $this->belongsTo(Generic::class, 'generic_id')->withTrashed();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id')->withTrashed();
    }
}