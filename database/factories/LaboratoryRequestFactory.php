<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LaboratoryRequest>
 */
class LaboratoryRequestFactory extends Factory
{
    protected $model = LaboratoryRequest::class;

    public function definition(): array
    {
        return [
            'consultation_id' => Consultation::factory(),
            'observations' => $this->faker->optional()->sentence(),
            'status' => 'pending',
        ];
    }
}
