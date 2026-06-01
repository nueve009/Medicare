<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => '63' . $this->faker->numerify('#########'),
            'birthdate' => $this->faker->date(),
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
