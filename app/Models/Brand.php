<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';

    protected $fillable = [
        'created_by',
        'generic_id',
        'brand_name',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generic()
    {
        return $this->belongsTo(Generic::class, 'generic_id');
    }
}