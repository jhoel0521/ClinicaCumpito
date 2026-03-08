<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Seeder;

class MedicationCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medications = [
            ['name' => 'Paracetamol', 'generic_name' => 'Paracetamol', 'pharmaceutical_form' => 'Jarabe', 'concentration' => '120mg/5ml'],
            ['name' => 'Paracetamol', 'generic_name' => 'Paracetamol', 'pharmaceutical_form' => 'Gotas', 'concentration' => '100mg/ml'],
            ['name' => 'Ibuprofeno', 'generic_name' => 'Ibuprofeno', 'pharmaceutical_form' => 'Suspensión', 'concentration' => '100mg/5ml'],
            ['name' => 'Amoxicilina', 'generic_name' => 'Amoxicilina', 'pharmaceutical_form' => 'Polvo para suspensión oral', 'concentration' => '250mg/5ml'],
            ['name' => 'Salbutamol', 'generic_name' => 'Salbutamol', 'pharmaceutical_form' => 'Inhalador', 'concentration' => '100mcg/dosis'],
        ];

        foreach ($medications as $medication) {
            Medication::create($medication);
        }
    }
}
