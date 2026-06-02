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

            $table->foreignId('consultation_id')
                  ->constrained('consultations')
                  ->onDelete('cascade');

            // Doctor selects a generic first, then a brand under that generic.
            // Both are required per business rules.
            $table->foreignId('generic_id')
                  ->constrained('generics')
                  ->onDelete('cascade');

            $table->foreignId('brand_id')
                  ->constrained('brands')
                  ->onDelete('cascade');

            $table->string('dosage');         // e.g. "500mg", "10mg/5ml"
            //Drop
            $table->string('frequency');      // e.g. "3x a day", "every 8 hours" 
            $table->string('duration');       // e.g. "7 days", "2 weeks"
            $table->text('instructions')->nullable(); // e.g. "Take after meals"

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};