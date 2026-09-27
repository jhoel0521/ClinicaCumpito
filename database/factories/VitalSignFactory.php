<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\VitalSign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VitalSign>
 */
class VitalSignFactory extends Factory
{
    protected $model = VitalSign::class;

    public function definition(): array
    {
        return [
            // Borrador: una consulta finalizada no admite cambios (tests deterministas).
            'consultation_id' => Consultation::factory()->draft(),
            'weight' => $this->faker->randomFloat(2, 3, 25),
            'height' => $this->faker->randomFloat(2, 50, 150),
            'head_circumference' => $this->faker->randomFloat(2, 30, 57),
            'temperature' => $this->faker->randomFloat(2, 36.5, 38.5),
        ];
    }
}
