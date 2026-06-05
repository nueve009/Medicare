<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->foreignId('clinic_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('clinics')
                  ->nullOnDelete();

            $table->foreignId('created_by')
                  ->nullable()
                  ->after('clinic_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('clinic_id');
            $table->dropConstrainedForeignId('created_by');
        });
    }
};