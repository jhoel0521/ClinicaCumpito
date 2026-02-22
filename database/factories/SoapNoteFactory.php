<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\SoapNote;
use Illuminate\Database\Eloquent\Factories\Factory;

class SoapNoteFactory extends Factory
{
    protected $model = SoapNote::class;

    public function definition(): array
    {
        return [
            'consultation_id' => Consultation::factory(),
            'subjective' => $this->faker->paragraph(),
            'objective' => $this->faker->paragraph(),
            'assessment' => $this->faker->paragraph(),
            'plan' => $this->faker->paragraph(),
        ];
    }
}
