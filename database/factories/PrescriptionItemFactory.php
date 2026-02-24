<?php

namespace Database\Factories;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PrescriptionItem>
 */
class PrescriptionItemFactory extends Factory
{
    protected $model = PrescriptionItem::class;

    public function definition(): array
    {
        return [
            'prescription_id' => Prescription::factory(),
            'source_template_item_id' => null,
            'medication_name' => $this->faker->words(2, true),
            'dose' => '5 ml',
            'frequency' => 'Cada 8 horas',
            'duration' => '5 días',
            'instructions' => $this->faker->optional()->sentence(),
        ];
    }
}
