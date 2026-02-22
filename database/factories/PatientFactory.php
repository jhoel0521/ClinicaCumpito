<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'responsible_doctor_id' => Doctor::factory(),
            'user_id' => null,
            'full_name' => $this->faker->name(),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', 'now'),
            'gender' => $this->faker->randomElement(['M', 'F']),
            'birth_weight' => $this->faker->randomFloat(2, 2, 5),
            'birth_height' => $this->faker->randomFloat(2, 45, 55),
            'birth_head_circumference' => $this->faker->randomFloat(2, 30, 38),
            'birth_type' => $this->faker->randomElement(['Normal', 'Cesarean']),
            'birth_place' => $this->faker->city(),
            'blood_group' => $this->faker->randomElement(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']),
            'chagas_status' => $this->faker->randomElement(['Positive', 'Negative', 'Not tested']),
            'syphilis_status' => $this->faker->randomElement(['Positive', 'Negative', 'Not tested']),
            'allergies' => $this->faker->optional()->sentence(),
            'pathologies' => $this->faker->optional()->sentence(),
            'surgeries' => $this->faker->optional()->sentence(),
        ];
    }

    public function withUser(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory(),
            ];
        });
    }
}
