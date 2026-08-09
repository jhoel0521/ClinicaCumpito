<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Consultation>
 */
class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'type' => $this->faker->randomElement(['digital', 'manual']),
            'status' => $this->faker->randomElement(['draft', 'saved', 'finalized']),
            'consultation_date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'scanned_file_path' => null,
            'scanned_file_name' => null,
            'pending_transcription' => false,
        ];
    }

    public function draft(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
            ];
        });
    }

    public function finalized(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'finalized',
            ];
        });
    }

    public function scanned(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'manual',
                'status' => 'draft',
                'doctor_id' => null,
                'scanned_file_path' => 'consultations/fake/scan.pdf',
                'scanned_file_name' => 'scan.pdf',
                'pending_transcription' => true,
            ];
        });
    }
}
