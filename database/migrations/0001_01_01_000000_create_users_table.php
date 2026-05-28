<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id'); // Using user_id as the primary key
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable(); // nullable in case a user doesn't provide one
            
            // Using an enum to strictly match your React Native UserRole types
            $table->enum('role', ['doctor', 'assistant', 'admin'])->default('assistant');
            
            $table->string('password'); // Required for Laravel Auth
            $table->rememberToken();
            $table->timestamps();
        });

        // Note: Laravel 11 also includes 'password_reset_tokens' and 'sessions' 
        // table creations in this file by default. Leave those exactly as they are below this!
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
