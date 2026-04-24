<?php

namespace Database\Factories;

use App\Models\PrescriptionTemplate;
use App\Models\PrescriptionTemplateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrescriptionTemplateItem>
 */
class PrescriptionTemplateItemFactory extends Factory
{
    protected $model = PrescriptionTemplateItem::class;

    public function definition(): array
    {
        return [
            'template_id' => PrescriptionTemplate::factory(),
            'custom_medication_name' => fake()->words(2, true),
            'dose' => '1 tableta',
            'frequency' => 'cada 8 horas',
            'duration' => '7 días',
            'instructions' => 'Tomar después de las comidas',
        ];
    }
}
