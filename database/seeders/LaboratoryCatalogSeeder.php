<?php

namespace Database\Seeders;

use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use Illuminate\Database\Seeder;

class LaboratoryCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hematologia = LaboratoryCategory::create([
            'name' => 'Hematología',
            'description' => 'Estudios de la sangre y sus componentes.',
        ]);

        LaboratoryExam::create([
            'category_id' => $hematologia->id,
            'name' => 'Hemograma Completo',
            'unit' => 'N/A',
            'reference_range' => 'Ver reporte',
        ]);

        $quimica = LaboratoryCategory::create([
            'name' => 'Química Sanguínea',
            'description' => 'Análisis bioquímicos de la sangre.',
        ]);

        LaboratoryExam::create([
            'category_id' => $quimica->id,
            'name' => 'Glucemia en ayunas',
            'unit' => 'mg/dL',
            'reference_range' => '70 - 100',
        ]);

        $uroanalisis = LaboratoryCategory::create([
            'name' => 'Uroanálisis',
            'description' => 'Exámenes de orina.',
        ]);

        LaboratoryExam::create([
            'category_id' => $uroanalisis->id,
            'name' => 'Examen General de Orina',
            'unit' => 'N/A',
            'reference_range' => 'Normal',
        ]);
    }
}
