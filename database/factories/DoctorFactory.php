<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'specialty' => $this->faker->randomElement(['Pediatrician', 'Cardiologist', 'Neurologist', 'General Practitioner']),
            'license_number' => $this->faker->unique()->numerify('MED-#####'),
            'active' => true,
        ];
    }

    public function withUser(): self
    {
        return $this->afterCreating(function (Doctor $doctor): void {
            User::factory()->create(['doctor_id' => $doctor->id]);
        });
    }
}
