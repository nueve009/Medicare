<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diseases', function (Blueprint $table) {
            // Custom Primary Key
            $table->id('disease_id'); 
            
            // Unique index prevents duplicate diseases from being added
            $table->string('disease_name')->unique();
            $table->text('description')->nullable();
            $table->text('symptoms')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // For archiving
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diseases');
    }
};