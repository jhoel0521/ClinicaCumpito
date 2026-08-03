<?php

namespace Database\Seeders;

use App\Models\Vaccine;
use Illuminate\Database\Seeder;

class VaccineCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Limpieza por cambios del esquema (obs clienta ago-2026):
        // - Hepatitis B al nacer eliminada del esquema.
        // - Influenza 2ª dosis movida de 7 a 12 meses.
        // Solo se borran registros sin aplicaciones cargadas (FK restrict en patient_vaccines).
        Vaccine::where('name', 'Hepatitis B')
            ->whereDoesntHave('patientVaccines')
            ->delete();
        Vaccine::where('name', 'Influenza')
            ->where('dose_sequence', 2)
            ->where('min_age_months', 7)
            ->whereDoesntHave('patientVaccines')
            ->delete();

        // Esquema PAI Bolivia (Programa Ampliado de Inmunización)
        $vaccines = [
            // Al nacer (0 meses)
            ['name' => 'BCG',             'disease_prevented' => 'Formas graves de Tuberculosis (meningitis, miliar)',     'recommended_age' => 'Al nacer',         'dose_sequence' => 1, 'min_age_months' => 0],

            // 2 meses
            ['name' => 'Pentavalente',    'disease_prevented' => 'Difteria, Tétanos, Coqueluche, Hepatitis B, Hib',       'recommended_age' => '2 meses',          'dose_sequence' => 1, 'min_age_months' => 2],
            ['name' => 'Antipolio IPV',   'disease_prevented' => 'Poliomielitis',                                          'recommended_age' => '2 meses',          'dose_sequence' => 1, 'min_age_months' => 2],
            ['name' => 'Antirotavirus',   'disease_prevented' => 'Diarrea grave por Rotavirus',                            'recommended_age' => '2 meses',          'dose_sequence' => 1, 'min_age_months' => 2],
            ['name' => 'Antineumocócica', 'disease_prevented' => 'Neumonía y meningitis por Streptococcus pneumoniae',     'recommended_age' => '2 meses',          'dose_sequence' => 1, 'min_age_months' => 2],

            // 4 meses
            ['name' => 'Pentavalente',    'disease_prevented' => 'Difteria, Tétanos, Coqueluche, Hepatitis B, Hib',       'recommended_age' => '4 meses',          'dose_sequence' => 2, 'min_age_months' => 4],
            ['name' => 'Antipolio OPV',   'disease_prevented' => 'Poliomielitis',                                          'recommended_age' => '4 meses',          'dose_sequence' => 2, 'min_age_months' => 4],
            ['name' => 'Antirotavirus',   'disease_prevented' => 'Diarrea grave por Rotavirus',                            'recommended_age' => '4 meses',          'dose_sequence' => 2, 'min_age_months' => 4],
            ['name' => 'Antineumocócica', 'disease_prevented' => 'Neumonía y meningitis por Streptococcus pneumoniae',     'recommended_age' => '4 meses',          'dose_sequence' => 2, 'min_age_months' => 4],

            // 6 meses
            ['name' => 'Pentavalente',    'disease_prevented' => 'Difteria, Tétanos, Coqueluche, Hepatitis B, Hib',       'recommended_age' => '6 meses',          'dose_sequence' => 3, 'min_age_months' => 6],
            ['name' => 'Antipolio OPV',   'disease_prevented' => 'Poliomielitis',                                          'recommended_age' => '6 meses',          'dose_sequence' => 3, 'min_age_months' => 6],
            ['name' => 'Antineumocócica', 'disease_prevented' => 'Neumonía y meningitis por Streptococcus pneumoniae',     'recommended_age' => '6 meses',          'dose_sequence' => 3, 'min_age_months' => 6],
            ['name' => 'Influenza',       'disease_prevented' => 'Influenza estacional',                                   'recommended_age' => '6 meses',          'dose_sequence' => 1, 'min_age_months' => 6],

            // 12 meses
            ['name' => 'Influenza',       'disease_prevented' => 'Influenza estacional',                                   'recommended_age' => '12 meses',         'dose_sequence' => 2, 'min_age_months' => 12],
            ['name' => 'SRP',             'disease_prevented' => 'Sarampión, Rubéola, Parotiditis',                        'recommended_age' => '12 meses',         'dose_sequence' => 1, 'min_age_months' => 12],
            ['name' => 'Antiamarílica',   'disease_prevented' => 'Fiebre Amarilla',                                        'recommended_age' => '12 meses',         'dose_sequence' => 1, 'min_age_months' => 12],
            ['name' => 'Antineumocócica', 'disease_prevented' => 'Neumonía y meningitis por Streptococcus pneumoniae',     'recommended_age' => '12 meses',         'dose_sequence' => 4, 'min_age_months' => 12],

            // 18 meses
            ['name' => 'DPT (Refuerzo)',  'disease_prevented' => 'Difteria, Tétanos, Coqueluche',                          'recommended_age' => '18 meses',         'dose_sequence' => 1, 'min_age_months' => 18],
            ['name' => 'Antipolio OPV',   'disease_prevented' => 'Poliomielitis',                                          'recommended_age' => '18 meses',         'dose_sequence' => 4, 'min_age_months' => 18],
            ['name' => 'Varicela',        'disease_prevented' => 'Varicela (Chickenpox)',                                   'recommended_age' => '18 meses',         'dose_sequence' => 1, 'min_age_months' => 18],

            // Anual (≥ 2 años)
            ['name' => 'Influenza',       'disease_prevented' => 'Influenza estacional',                                   'recommended_age' => 'Anual (≥ 2 años)', 'dose_sequence' => 3, 'min_age_months' => 24],

            // 4 - 5 años
            ['name' => 'SRP (Refuerzo)',  'disease_prevented' => 'Sarampión, Rubéola, Parotiditis',                        'recommended_age' => '4 - 5 años',       'dose_sequence' => 2, 'min_age_months' => 48],
            ['name' => 'Antipolio OPV',   'disease_prevented' => 'Poliomielitis',                                          'recommended_age' => '4 - 5 años',       'dose_sequence' => 5, 'min_age_months' => 48],
            ['name' => 'DPT (Refuerzo)',  'disease_prevented' => 'Difteria, Tétanos, Coqueluche',                          'recommended_age' => '4 - 5 años',       'dose_sequence' => 2, 'min_age_months' => 48],

            // 10 - 14 años (niñas)
            ['name' => 'VPH',            'disease_prevented' => 'Cáncer Cérvico-Uterino (VPH 16 y 18)',                   'recommended_age' => '10 - 14 años',     'dose_sequence' => 1, 'min_age_months' => 120],
            ['name' => 'VPH',            'disease_prevented' => 'Cáncer Cérvico-Uterino (VPH 16 y 18)',                   'recommended_age' => '10 - 14 años',     'dose_sequence' => 2, 'min_age_months' => 126],
        ];

        foreach ($vaccines as $vaccine) {
            Vaccine::firstOrCreate(
                [
                    'name' => $vaccine['name'],
                    'dose_sequence' => $vaccine['dose_sequence'],
                    'min_age_months' => $vaccine['min_age_months'],
                ],
                [
                    'disease_prevented' => $vaccine['disease_prevented'],
                    'recommended_age' => $vaccine['recommended_age'],
                ]
            );
        }

        $this->command->info('✔ Vacunas PAI Bolivia: '.count($vaccines).' registros cargados.');
    }
}
