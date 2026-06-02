<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            
            // Explicitly point the foreign key to the 'generic_id' column on the 'generics' table
            $table->foreignId('generic_id')->constrained('generics')->onDelete('cascade');
            
            $table->string('brand_name')->unique();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};