<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_diseases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consultation_id')
                  ->constrained('consultations')
                  ->onDelete('cascade');

            $table->foreignId('disease_id')
                  ->constrained('diseases')
                  ->onDelete('cascade');

            //Drop
            $table->enum('type', ['primary', 'secondary'])->default('primary');

            $table->timestamps();

            // A disease can only be attached once per consultation.
            $table->unique(['consultation_id', 'disease_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_diseases');
    }
};