<?php

namespace Database\Seeders;

use App\Models\Vaccine;
use Illuminate\Database\Seeder;

class VaccineCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Esquema PAI Bolivia (Programa Ampliado de Inmunización)
        $vaccines = [
            // Al nacer
            ['name' => 'BCG',              'disease_prevented' => 'Formas graves de Tuberculosis (meningitis, miliar)', 'recommended_age' => 'Al nacer',       'dose_sequence' => 1],
            ['name' => 'Hepatitis B',      'disease_prevented' => 'Hepatitis B',                                         'recommended_age' => 'Al nacer',       'dose_sequence' => 1],

            // 2 meses
            ['name' => 'Pentavalente',     'disease_prevented' => 'Difteria, Tétanos, Coqueluche, Hepatitis B, Hib',    'recommended_age' => '2 meses',        'dose_sequence' => 1],
            ['name' => 'Antipolio OPV',    'disease_prevented' => 'Poliomielitis',                                       'recommended_age' => '2 meses',        'dose_sequence' => 1],
            ['name' => 'Antirotavirus',    'disease_prevented' => 'Diarrea grave por Rotavirus',                         'recommended_age' => '2 meses',        'dose_sequence' => 1],
            ['name' => 'Antineumocócica',  'disease_prevented' => 'Neumonía y meningitis por Streptococcus pneumoniae',  'recommended_age' => '2 meses',        'dose_sequence' => 1],

            // 4 meses
            ['name' => 'Pentavalente',     'disease_prevented' => 'Difteria, Tétanos, Coqueluche, Hepatitis B, Hib',    'recommended_age' => '4 meses',        'dose_sequence' => 2],
            ['name' => 'Antipolio OPV',    'disease_prevented' => 'Poliomielitis',                                       'recommended_age' => '4 meses',        'dose_sequence' => 2],
            ['name' => 'Antirotavirus',    'disease_prevented' => 'Diarrea grave por Rotavirus',                         'recommended_age' => '4 meses',        'dose_sequence' => 2],
            ['name' => 'Antineumocócica',  'disease_prevented' => 'Neumonía y meningitis por Streptococcus pneumoniae',  'recommended_age' => '4 meses',        'dose_sequence' => 2],

            // 6 meses
            ['name' => 'Pentavalente',     'disease_prevented' => 'Difteria, Tétanos, Coqueluche, Hepatitis B, Hib',    'recommended_age' => '6 meses',        'dose_sequence' => 3],
            ['name' => 'Antipolio OPV',    'disease_prevented' => 'Poliomielitis',                                       'recommended_age' => '6 meses',        'dose_sequence' => 3],
            ['name' => 'Antineumocócica',  'disease_prevented' => 'Neumonía y meningitis por Streptococcus pneumoniae',  'recommended_age' => '6 meses',        'dose_sequence' => 3],
            ['name' => 'Influenza',        'disease_prevented' => 'Influenza estacional',                                 'recommended_age' => '6 meses',        'dose_sequence' => 1],

            // 7 meses
            ['name' => 'Influenza',        'disease_prevented' => 'Influenza estacional',                                 'recommended_age' => '7 meses',        'dose_sequence' => 2],

            // 12 meses
            ['name' => 'SRP',              'disease_prevented' => 'Sarampión, Rubéola, Parotiditis',                     'recommended_age' => '12 meses',       'dose_sequence' => 1],
            ['name' => 'Antiamarílica',    'disease_prevented' => 'Fiebre Amarilla',                                     'recommended_age' => '12 meses',       'dose_sequence' => 1],
            ['name' => 'Antineumocócica',  'disease_prevented' => 'Neumonía y meningitis por Streptococcus pneumoniae',  'recommended_age' => '12 meses',       'dose_sequence' => 4],

            // 18 meses
            ['name' => 'Antipolio OPV',    'disease_prevented' => 'Poliomielitis',                                       'recommended_age' => '18 meses',       'dose_sequence' => 4],
            ['name' => 'DPT (Refuerzo)',   'disease_prevented' => 'Difteria, Tétanos, Coqueluche',                       'recommended_age' => '18 meses',       'dose_sequence' => 4],
            ['name' => 'Varicela',         'disease_prevented' => 'Varicela (Chickenpox)',                                'recommended_age' => '18 meses',       'dose_sequence' => 1],

            // Anual
            ['name' => 'Influenza',        'disease_prevented' => 'Influenza estacional',                                 'recommended_age' => 'Anual (≥ 2 años)', 'dose_sequence' => 3],

            // 4 - 5 años
            ['name' => 'SRP (Refuerzo)',   'disease_prevented' => 'Sarampión, Rubéola, Parotiditis',                     'recommended_age' => '4 - 5 años',     'dose_sequence' => 2],
            ['name' => 'Antipolio OPV',    'disease_prevented' => 'Poliomielitis',                                       'recommended_age' => '4 - 5 años',     'dose_sequence' => 5],
            ['name' => 'DPT (Refuerzo)',   'disease_prevented' => 'Difteria, Tétanos, Coqueluche',                       'recommended_age' => '4 - 5 años',     'dose_sequence' => 5],

            // 10 - 14 años
            ['name' => 'VPH',             'disease_prevented' => 'Cáncer Cérvico-Uterino (VPH 16 y 18)',                'recommended_age' => '10 - 14 años',   'dose_sequence' => 1],
            ['name' => 'VPH',             'disease_prevented' => 'Cáncer Cérvico-Uterino (VPH 16 y 18)',                'recommended_age' => '10 - 14 años',   'dose_sequence' => 2],
        ];

        foreach ($vaccines as $vaccine) {
            Vaccine::firstOrCreate(
                [
                    'name' => $vaccine['name'],
                    'recommended_age' => $vaccine['recommended_age'],
                    'dose_sequence' => $vaccine['dose_sequence'],
                ],
                ['disease_prevented' => $vaccine['disease_prevented']]
            );
        }

        $this->command->info('✔ Vacunas PAI Bolivia: '.count($vaccines).' registros cargados.');
    }
}
