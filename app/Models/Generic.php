<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Generic extends Model
{
    protected $fillable = [
        'generic_name',
    ];

    // Define the relationship to the Brand model
    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
}
