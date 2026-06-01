<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Generic extends Model
{
    use HasFactory, SoftDeletes;

    // Explicitly tell Laravel the primary key is generic_id
    protected $primaryKey = 'generic_id';

    protected $fillable = [
        'generic_name',
    ];

    // Define the relationship to the Brand model
    public function brands()
    {
        return $this->hasMany(Brand::class, 'generic_id', 'generic_id');
    }
}