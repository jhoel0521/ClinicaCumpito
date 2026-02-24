<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\LaboratoryTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryTemplate>
 */
class LaboratoryTemplateFactory extends Factory
{
    protected $model = LaboratoryTemplate::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
