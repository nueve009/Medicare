<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id(); // Automatically creates an auto-incrementing primary key
            
            // FK to the user who created this record
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            // Link the patient to a specific clinic
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birthdate');
            $table->string('email')->unique()->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('blood_type', 5)->nullable();

            $table->enum('civil_status', ['single', 'married', 'divorced', 'separated', 'widowed', 'minor'])->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('temp', 5, 2)->nullable();
            $table->string('bp', 10)->nullable();
            $table->text('allergies')->nullable();
            
            $table->timestamps(); // Automatically adds created_at and updated_at
            $table->softDeletes(); // Automatically adds deleted_at for safe archiving
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};