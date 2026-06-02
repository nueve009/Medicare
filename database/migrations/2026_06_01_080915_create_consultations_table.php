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

            $table->foreignId('doctor_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->onDelete('cascade');

            // Denormalized for fast clinic-scoped queries without joining patients.
            $table->foreignId('clinic_id')
                  ->constrained('clinics')
                  ->onDelete('cascade');

            //Drop
            $table->text('chief_complaint')->nullable();

            $table->text('notes')->nullable();
            $table->datetime('consultation_date');

            // --- Vital signs (all nullable — recording them is optional) ---
            $table->string('blood_pressure', 10)->nullable();      // e.g. "120/80"
            $table->unsignedSmallInteger('heart_rate')->nullable();        // bpm
            $table->unsignedSmallInteger('respiratory_rate')->nullable();  // breaths/min
            $table->decimal('temperature', 4, 1)->nullable();      // e.g. 36.5 °C
            $table->decimal('weight', 5, 2)->nullable();            // kg
            $table->decimal('height', 5, 2)->nullable();            // cm
            $table->unsignedSmallInteger('oxygen_saturation')->nullable(); // %

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};