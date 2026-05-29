<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes; // Enable soft deletes

    protected $fillable = [
        'created_by',
        'first_name',
        'last_name',
        'gender',
        'birthdate',
        'email',
        'phone_number',
        'address',
        'blood_type'
    ];

    // Define the relationship to the User who created the patient
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}