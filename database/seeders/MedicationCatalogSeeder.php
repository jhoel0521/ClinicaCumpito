<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Seeder;

class MedicationCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $medications = [
            // Antipiréticos / Analgésicos
            ['name' => 'Paracetamol', 'generic_name' => 'Paracetamol', 'pharmaceutical_form' => 'Jarabe',      'concentration' => '120 mg/5 ml'],
            ['name' => 'Paracetamol', 'generic_name' => 'Paracetamol', 'pharmaceutical_form' => 'Gotas',       'concentration' => '100 mg/ml'],
            ['name' => 'Ibuprofeno',  'generic_name' => 'Ibuprofeno',  'pharmaceutical_form' => 'Suspensión',  'concentration' => '100 mg/5 ml'],

            // Antibióticos
            ['name' => 'Amoxicilina',                        'generic_name' => 'Amoxicilina',                        'pharmaceutical_form' => 'Polvo para suspensión oral', 'concentration' => '250 mg/5 ml'],
            ['name' => 'Amoxicilina + Ácido Clavulánico',    'generic_name' => 'Amoxicilina/Clavulanato',            'pharmaceutical_form' => 'Polvo para suspensión oral', 'concentration' => '250 mg + 62.5 mg/5 ml'],
            ['name' => 'Azitromicina',                        'generic_name' => 'Azitromicina',                       'pharmaceutical_form' => 'Polvo para suspensión oral', 'concentration' => '200 mg/5 ml'],
            ['name' => 'Trimetoprim/Sulfametoxazol (TMP-SMX)', 'generic_name' => 'Cotrimoxazol',                       'pharmaceutical_form' => 'Suspensión',                 'concentration' => '40 mg + 200 mg/5 ml'],
            ['name' => 'Cefalexina',                          'generic_name' => 'Cefalexina',                         'pharmaceutical_form' => 'Polvo para suspensión oral', 'concentration' => '250 mg/5 ml'],
            ['name' => 'Metronidazol',                        'generic_name' => 'Metronidazol',                       'pharmaceutical_form' => 'Suspensión',                 'concentration' => '125 mg/5 ml'],
            ['name' => 'Eritromicina',                        'generic_name' => 'Eritromicina',                       'pharmaceutical_form' => 'Polvo para suspensión oral', 'concentration' => '200 mg/5 ml'],

            // Broncodilatadores / Respiratorios
            ['name' => 'Salbutamol',   'generic_name' => 'Salbutamol',   'pharmaceutical_form' => 'Inhalador MDI',         'concentration' => '100 mcg/dosis'],
            ['name' => 'Salbutamol',   'generic_name' => 'Salbutamol',   'pharmaceutical_form' => 'Solución nebulización', 'concentration' => '5 mg/ml'],
            ['name' => 'Budesonida',   'generic_name' => 'Budesonida',   'pharmaceutical_form' => 'Solución nebulización', 'concentration' => '0.5 mg/2 ml'],
            ['name' => 'Suero nasal',  'generic_name' => 'Cloruro sódico', 'pharmaceutical_form' => 'Solución nasal',       'concentration' => '0.9%'],

            // Antieméticos / Gastrointestinales
            ['name' => 'Dimenhidrinato',              'generic_name' => 'Dimenhidrinato',        'pharmaceutical_form' => 'Jarabe',    'concentration' => '12.5 mg/5 ml'],
            ['name' => 'Ondansetrón',                 'generic_name' => 'Ondansetrón',           'pharmaceutical_form' => 'Solución oral', 'concentration' => '4 mg/5 ml'],
            ['name' => 'Sales de Rehidratación Oral', 'generic_name' => 'SRO OMS',               'pharmaceutical_form' => 'Polvo',     'concentration' => 'Sobre 200 ml'],
            ['name' => 'Zinc',                        'generic_name' => 'Sulfato de zinc',       'pharmaceutical_form' => 'Jarabe',    'concentration' => '10 mg/5 ml'],
            ['name' => 'Diosmectita',                 'generic_name' => 'Diosmectita',           'pharmaceutical_form' => 'Polvo',     'concentration' => '3 g/sobre'],

            // Antiparasitarios
            ['name' => 'Albendazol',   'generic_name' => 'Albendazol',   'pharmaceutical_form' => 'Suspensión', 'concentration' => '400 mg/10 ml'],
            ['name' => 'Mebendazol',   'generic_name' => 'Mebendazol',   'pharmaceutical_form' => 'Suspensión', 'concentration' => '100 mg/5 ml'],
            ['name' => 'Metronidazol', 'generic_name' => 'Metronidazol', 'pharmaceutical_form' => 'Comprimidos', 'concentration' => '500 mg'],

            // Antihistamínicos
            ['name' => 'Loratadina',      'generic_name' => 'Loratadina',      'pharmaceutical_form' => 'Jarabe',  'concentration' => '5 mg/5 ml'],
            ['name' => 'Cetirizina',      'generic_name' => 'Cetirizina',      'pharmaceutical_form' => 'Jarabe',  'concentration' => '5 mg/5 ml'],
            ['name' => 'Difenhidramina',  'generic_name' => 'Difenhidramina',  'pharmaceutical_form' => 'Jarabe',  'concentration' => '12.5 mg/5 ml'],

            // Vitaminas / Suplementos
            ['name' => 'Vitamina C',       'generic_name' => 'Ácido ascórbico',   'pharmaceutical_form' => 'Jarabe',    'concentration' => '100 mg/5 ml'],
            ['name' => 'Vitamina D3',      'generic_name' => 'Colecalciferol',    'pharmaceutical_form' => 'Gotas',     'concentration' => '400 UI/gota'],
            ['name' => 'Sulfato ferroso',  'generic_name' => 'Hierro elemental',  'pharmaceutical_form' => 'Jarabe',    'concentration' => '25 mg/5 ml'],
            ['name' => 'Ácido fólico',     'generic_name' => 'Ácido fólico',      'pharmaceutical_form' => 'Comprimidos', 'concentration' => '5 mg'],
            ['name' => 'Vitamina A',       'generic_name' => 'Retinol',           'pharmaceutical_form' => 'Cápsulas', 'concentration' => '100.000 UI'],
            ['name' => 'Complejo B',       'generic_name' => 'Vitaminas B1-B6-B12', 'pharmaceutical_form' => 'Jarabe', 'concentration' => '100 mg/5 ml'],
        ];

        foreach ($medications as $med) {
            Medication::firstOrCreate(
                [
                    'name' => $med['name'],
                    'pharmaceutical_form' => $med['pharmaceutical_form'],
                    'concentration' => $med['concentration'],
                ],
                ['generic_name' => $med['generic_name']]
            );
        }

        $this->command->info('✔ Medicamentos: '.count($medications).' registros cargados.');
    }
}
