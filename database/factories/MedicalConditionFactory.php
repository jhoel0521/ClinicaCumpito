<?php

namespace Database\Factories;

use App\Models\MedicalCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalCondition>
 */
class MedicalConditionFactory extends Factory
{
    /** @var class-string<\App\Models\MedicalCondition> */
    protected $model = MedicalCondition::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
