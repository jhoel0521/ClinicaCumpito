<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\SoapNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SoapNote>
 */
class SoapNoteFactory extends Factory
{
    protected $model = SoapNote::class;

    public function definition(): array
    {
        return [
            // Borrador: una consulta finalizada no admite cambios (tests deterministas).
            'consultation_id' => Consultation::factory()->draft(),
            'subjective' => $this->faker->paragraph(),
            'objective' => $this->faker->paragraph(),
            'assessment' => $this->faker->paragraph(),
            'plan' => $this->faker->paragraph(),
        ];
    }
}
