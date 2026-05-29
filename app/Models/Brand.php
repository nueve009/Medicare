<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'generic_id',
        'brand_name',
    ];

    // Define the relationship to the Generic model
    public function generic()
    {
        return $this->belongsTo(Generic::class);
    }
}
