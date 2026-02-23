<?php

namespace Database\Factories;

use App\Models\MedicalCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalConditionFactory extends Factory
{
    protected $model = MedicalCondition::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
