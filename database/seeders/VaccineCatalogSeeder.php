<?php

namespace Database\Seeders;

use App\Models\Vaccine;
use Illuminate\Database\Seeder;

class VaccineCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vaccines = [
            ['name' => 'BCG', 'disease_prevented' => 'Formas graves de Tuberculosis', 'recommended_age' => 'Al nacer', 'dose_sequence' => 1],
            ['name' => 'Pentavalente (1ra)', 'disease_prevented' => 'Difteria, Tétanos, Coqueluche, Hep B, Hib', 'recommended_age' => '2 meses', 'dose_sequence' => 1],
            ['name' => 'Pentavalente (2da)', 'disease_prevented' => 'Difteria, Tétanos, Coqueluche, Hep B, Hib', 'recommended_age' => '4 meses', 'dose_sequence' => 2],
            ['name' => 'Pentavalente (3ra)', 'disease_prevented' => 'Difteria, Tétanos, Coqueluche, Hep B, Hib', 'recommended_age' => '6 meses', 'dose_sequence' => 3],
            ['name' => 'Antipolio (1ra)', 'disease_prevented' => 'Poliomielitis', 'recommended_age' => '2 meses', 'dose_sequence' => 1],
            ['name' => 'Antipolio (2da)', 'disease_prevented' => 'Poliomielitis', 'recommended_age' => '4 meses', 'dose_sequence' => 2],
            ['name' => 'Antipolio (3ra)', 'disease_prevented' => 'Poliomielitis', 'recommended_age' => '6 meses', 'dose_sequence' => 3],
            ['name' => 'Antirotavirus (1ra)', 'disease_prevented' => 'Diarreas severas por rotavirus', 'recommended_age' => '2 meses', 'dose_sequence' => 1],
            ['name' => 'Antirotavirus (2da)', 'disease_prevented' => 'Diarreas severas por rotavirus', 'recommended_age' => '4 meses', 'dose_sequence' => 2],
            ['name' => 'Antineumocócica (1ra)', 'disease_prevented' => 'Neumonías y meningitis por neumococo', 'recommended_age' => '2 meses', 'dose_sequence' => 1],
            ['name' => 'Antineumocócica (2da)', 'disease_prevented' => 'Neumonías y meningitis por neumococo', 'recommended_age' => '4 meses', 'dose_sequence' => 2],
            ['name' => 'Antineumocócica (3ra)', 'disease_prevented' => 'Neumonías y meningitis por neumococo', 'recommended_age' => '6 meses', 'dose_sequence' => 3],
            ['name' => 'SRP (1ra)', 'disease_prevented' => 'Sarampión, Rubeola, Parotiditis', 'recommended_age' => '12 a 23 meses', 'dose_sequence' => 1],
            ['name' => 'Antiamarílica', 'disease_prevented' => 'Fiebre Amarilla', 'recommended_age' => '12 a 23 meses', 'dose_sequence' => 1],
            ['name' => 'VPH', 'disease_prevented' => 'Cáncer Cérvico Uterino', 'recommended_age' => '10 a 14 años', 'dose_sequence' => 1],
        ];

        foreach ($vaccines as $vaccine) {
            Vaccine::create($vaccine);
        }
    }
}
