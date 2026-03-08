<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medication>
 */
class MedicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'generic_name' => $this->faker->word(),
            'pharmaceutical_form' => $this->faker->randomElement(['Tableta', 'Jarabe', 'Suspensión', 'Inyectable', 'Crema']),
            'concentration' => $this->faker->randomElement(['500mg', '250mg/5ml', '10%', '1g']),
        ];
    }
}
