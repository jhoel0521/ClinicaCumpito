<?php

namespace Database\Factories;

use App\Models\Medication;
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
            'medication_id' => Medication::factory(),
            'custom_medication_name' => null,
            'dose' => '1 tableta',
            'frequency' => 'cada 8 horas',
            'duration' => '7 días',
            'instructions' => 'Tomar después de las comidas',
        ];
    }
}
