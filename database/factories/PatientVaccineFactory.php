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
        $consultation = Consultation::factory()->create();

        return [
            'patient_id' => $consultation->patient_id,
            'consultation_id' => $consultation->id,
            'vaccine_id' => Vaccine::factory(),
            'applied_by_doctor_id' => null,
            'application_site' => $this->faker->optional()->company(),
            'applied_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'dose_number' => $this->faker->numberBetween(1, 4),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
