<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generics', function (Blueprint $table) {
            $table->dropUnique(['generic_name']);
            $table->foreignId('created_by')
                  ->nullable()
                  ->after('id')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('generics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->unique('generic_name');
        });
    }
};