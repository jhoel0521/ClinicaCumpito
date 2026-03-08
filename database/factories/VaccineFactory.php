<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vaccine>
 */
class VaccineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['BCG', 'Pentavalente', 'Polio', 'Rotavirus', 'Neumocócica', 'SRP']),
            'disease_prevented' => $this->faker->sentence(),
            'recommended_age' => $this->faker->randomElement(['Al nacer', '2 meses', '4 meses', '6 meses', '12 meses', '18 meses', '4 años']),
            'dose_sequence' => $this->faker->numberBetween(1, 3),
        ];
    }
}
