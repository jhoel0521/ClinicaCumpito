<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\LaboratoryItemResult;
use App\Models\LaboratoryRequestItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LaboratoryItemResult>
 */
class LaboratoryItemResultFactory extends Factory
{
    protected $model = LaboratoryItemResult::class;

    public function definition(): array
    {
        return [
            'laboratory_request_item_id' => LaboratoryRequestItem::factory(),
            'consultation_id' => Consultation::factory(),
            'value' => $this->faker->randomFloat(2, 0, 100),
            'report_text' => null,
            'is_abnormal' => false,
            'sort_order' => 0,
        ];
    }
}
