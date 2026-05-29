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
            $table->foreignId('created_by')->constrained('users', 'user_id')->onDelete('cascade');
            
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();;
            $table->date('birthdate');
            $table->string('email')->unique()->nullable();
            $table->string('phone_number', 11)->nullable();
            $table->text('address')->nullable();
            $table->string('blood_type', 5)->nullable();
            
            $table->timestamps(); // Automatically adds created_at and updated_at
            $table->softDeletes(); // Automatically adds deleted_at for safe archiving
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};