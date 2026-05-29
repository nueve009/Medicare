<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            
            // Link to the specific visit
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            
            // Link to the medicine. Making both nullable allows the doctor to prescribe either a specific brand OR a generic equivalent.
            $table->foreignId('generic_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            
            // Medical Instructions
            $table->string('dosage'); // e.g., "500mg"
            $table->string('frequency'); // e.g., "3 times a day"
            $table->string('duration'); // e.g., "7 days"
            $table->text('special_instructions')->nullable(); // e.g., "Take after meals"
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};