<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LaboratoryExam>
 */
class LaboratoryExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => \App\Models\LaboratoryCategory::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'unit' => $this->faker->randomElement(['mg/dL', 'g/dL', 'u/L', 'mm/h', '%', 'cells/uL']),
            'reference_range' => '0 - 100',
        ];
    }
}
