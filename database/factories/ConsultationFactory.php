<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
            // Relativa a now() (respeta Carbon::setTestNow) y siempre posterior al
            // nacimiento del paciente.
            'consultation_date' => function (array $attributes) {
                $to = now()->subDay();
                $from = now()->subMonths(6);

                $birthDate = Patient::query()->whereKey($attributes['patient_id'])->value('date_of_birth');
                if ($birthDate !== null) {
                    $minDate = Carbon::parse($birthDate)->addDay();
                    $from = $minDate->greaterThan($from) ? $minDate : $from;
                }

                return $this->faker->dateTimeBetween($from, $from->greaterThan($to) ? $from : $to);
            },
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

    public function configure(): static
    {
        return $this->afterMaking(function (Consultation $consultation): void {
            // La fecha de consulta nunca puede ser anterior al nacimiento del
            // paciente (Age lanza "fecha de nacimiento no puede ser futura").
            $patient = Patient::query()->find($consultation->patient_id);

            if ($patient?->date_of_birth === null) {
                return;
            }

            // Si el test fijó una fecha de consulta anterior al nacimiento
            // (aleatorio) del paciente, se respeta la fecha de la consulta y se
            // corrige el nacimiento: mover la consulta rompía los tests que
            // verifican esa fecha.
            if ($consultation->consultation_date < $patient->date_of_birth->copy()->addDay()) {
                $patient->update([
                    'date_of_birth' => Carbon::parse($consultation->consultation_date)->subYear()->toDateString(),
                ]);
            }
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
