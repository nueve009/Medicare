<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id(); 
            
            // Foreign Keys
            // Assuming your patients table uses the default 'id'
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade'); 
            
            // Referencing the custom 'user_id' we set on the users table
            $table->foreignId('doctor_id')->constrained('users', 'user_id')->onDelete('cascade');
            
            // Referencing the custom 'disease_id' on the diseases table (nullable because a diagnosis might not be immediate)
            $table->foreignId('disease_id')->nullable()->constrained('diseases')->onDelete('set null');

            // Consultation Data
            $table->dateTime('consultation_date');
            $table->text('symptoms')->nullable();
            $table->text('diagnosis_notes')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};