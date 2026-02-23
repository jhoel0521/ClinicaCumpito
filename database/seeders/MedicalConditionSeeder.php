<?php

namespace Database\Seeders;

use App\Models\MedicalCondition;
use Illuminate\Database\Seeder;

class MedicalConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            [
                'name' => 'Chagas',
                'description' => 'Enfermedad de Chagas, también conocida como tripanosomiasis americana',
            ],
            [
                'name' => 'Syphilis',
                'description' => 'Sífilis, infección de transmisión sexual causada por Treponema pallidum',
            ],
        ];

        foreach ($conditions as $condition) {
            MedicalCondition::firstOrCreate(
                ['name' => $condition['name']],
                $condition
            );
        }
    }
}
