<?php

namespace Database\Factories;

use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LaboratoryRequestItem>
 */
class LaboratoryRequestItemFactory extends Factory
{
    protected $model = LaboratoryRequestItem::class;

    public function definition(): array
    {
        return [
            'laboratory_request_id' => LaboratoryRequest::factory(),
            'exam_name' => $this->faker->words(3, true),
        ];
    }
}
