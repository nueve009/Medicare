<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_diseases', function (Blueprint $table) {
            $table->string('disease_name_snapshot')->after('disease_id');
            $table->text('symptoms')->nullable()->after('disease_name_snapshot');
            $table->enum('status', ['ongoing', 'treated', 'referred'])
                  ->default('ongoing')
                  ->after('symptoms');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_diseases', function (Blueprint $table) {
            $table->dropColumn(['disease_name_snapshot', 'symptoms', 'status']);
        });
    }
};