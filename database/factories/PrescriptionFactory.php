<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prescription>
 */
class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'consultation_id' => Consultation::factory(),
            'observations' => $this->faker->optional()->sentence(),
        ];
    }
}
