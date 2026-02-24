<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\PatientVaccine;
use App\Models\Vaccine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PatientVaccine>
 */
class PatientVaccineFactory extends Factory
{
    protected $model = PatientVaccine::class;

    public function definition(): array
    {
        return [
            'consultation_id' => Consultation::factory(),
            'vaccine_id' => Vaccine::factory(),
            'applied_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'dose_number' => $this->faker->numberBetween(1, 4),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
