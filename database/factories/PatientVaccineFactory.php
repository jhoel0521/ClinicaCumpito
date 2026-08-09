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
            'patient_id' => null,
            'vaccine_id' => Vaccine::factory(),
            'applied_by_doctor_id' => null,
            'application_site' => $this->faker->optional()->company(),
            'applied_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            'dose_number' => $this->faker->numberBetween(1, 4),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (PatientVaccine $patientVaccine): void {
            if ($patientVaccine->consultation_id && ! $patientVaccine->patient_id) {
                $consultation = Consultation::query()->find($patientVaccine->consultation_id);
                if ($consultation) {
                    $patientVaccine->patient_id = $consultation->patient_id;
                }
            }
        })->afterCreating(function (PatientVaccine $patientVaccine): void {
            if ($patientVaccine->consultation_id && ! $patientVaccine->patient_id) {
                $consultation = Consultation::query()->find($patientVaccine->consultation_id);
                if ($consultation) {
                    $patientVaccine->update(['patient_id' => $consultation->patient_id]);
                }
            }
        });
    }
}
