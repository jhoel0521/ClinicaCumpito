<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\PrescriptionTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrescriptionTemplate>
 */
class PrescriptionTemplateFactory extends Factory
{
    protected $model = PrescriptionTemplate::class;

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
