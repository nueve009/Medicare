<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->dropUnique(['disease_name']);
        });
    }

    public function down(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->unique('disease_name');
        });
    }
};