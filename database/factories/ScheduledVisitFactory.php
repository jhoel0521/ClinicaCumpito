<?php

namespace Database\Factories;

use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduledVisit>
 */
class ScheduledVisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => null,
            'created_by_user_id' => null,
            'scheduled_for' => now()->addDays(7)->format('Y-m-d'),
            'reason' => $this->faker->randomElement(['Control', 'Traer laboratorio', 'Control 15 meses']),
            // Se programó 10 días antes de la fecha (sin pasar de hoy): las consultas
            // anteriores a este momento no cuentan como asistencia.
            'created_at' => fn (array $attributes) => Carbon::parse($attributes['scheduled_for'])->subDays(10)->min(now()),
        ];
    }
}
