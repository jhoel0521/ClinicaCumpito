<?php

namespace Database\Factories;

use App\Models\LaboratoryExam;
use App\Models\LaboratoryTemplate;
use App\Models\LaboratoryTemplateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryTemplateItem>
 */
class LaboratoryTemplateItemFactory extends Factory
{
    protected $model = LaboratoryTemplateItem::class;

    public function definition(): array
    {
        return [
            'template_id' => LaboratoryTemplate::factory(),
            'laboratory_exam_id' => LaboratoryExam::factory(),
            'indications' => $this->faker->sentence(),
        ];
    }
}
