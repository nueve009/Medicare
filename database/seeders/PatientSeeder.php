<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        // This will create exactly 20 dummy patients
        Patient::factory()->count(20)->create();
    }
}
