<?php

namespace Database\Seeders;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\VitalSign;
use Illuminate\Database\Seeder;

/**
 * Datos de prueba para gráficas OMS de crecimiento.
 *
 * Crea dos pacientes con historial de controles pediátricos de 0 a 48 meses,
 * siguiendo una evolución estándar cercana a la mediana OMS.
 *
 * Pacientes:
 *  - Aitana Aguilar (F, 4 años) — Cercana a mediana/SD0
 *  - Thiago Méndez  (M, 4 años) — Ligeramente sobre mediana/SD0
 *
 * Cada control genera una Consultation + VitalSign con talla y perímetro cefálico.
 * El peso de nacimiento se registra como birth_weight + primer vital sign.
 */
class GrowthChartTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = Doctor::firstOrCreate(
            ['license_number' => 'MP-001'],
            [
                'full_name' => 'Dr. Carlos García',
                'specialty' => 'Pediatría',
                'active' => true,
            ]
        );

        $this->seedAitana($doctor);
        $this->seedThiago($doctor);

        $this->command->info('✔ Pacientes de prueba para gráficas OMS creados:');
        $this->command->table(
            ['Paciente', 'Género', 'Controles', 'Rango'],
            [
                ['Aitana Aguilar', 'F', '13', '0-48 meses'],
                ['Thiago Méndez', 'M', '11', '0-48 meses'],
            ]
        );
    }

    private function seedAitana(Doctor $doctor): void
    {
        $dob = now()->subMonths(48)->startOfDay();

        $patient = Patient::updateOrCreate(
            ['full_name' => 'Aitana Aguilar'],
            [
                'responsible_doctor_id' => $doctor->id,
                'date_of_birth' => $dob,
                'gender' => 'F',
                'birth_weight' => 3.2,
                'birth_height' => 49.1,
                'birth_head_circumference' => 33.9,
                'birth_type' => 'Normal',
                'birth_place' => 'Cochabamba',
                'blood_group' => 'O+',
            ]
        );

        // Controles: [meses, talla_cm, perimetro_cefalico_cm, peso_kg]
        // Pesos estimados siguiendo las curvas OMS para una niña cercana a mediana
        $controles = [
            ['m' => 0,  't' => 49.1,  'pc' => 33.9, 'w' => 3.2],
            ['m' => 1,  't' => 53.7,  'pc' => 36.5, 'w' => 4.2],
            ['m' => 2,  't' => 57.1,  'pc' => 38.3, 'w' => 5.1],
            ['m' => 4,  't' => 62.1,  'pc' => 40.6, 'w' => 6.4],
            ['m' => 6,  't' => 65.7,  'pc' => 41.5, 'w' => 7.3],
            ['m' => 9,  't' => 70.1,  'pc' => 43.5, 'w' => 8.2],
            ['m' => 12, 't' => 74.0,  'pc' => 45.0, 'w' => 8.9],
            ['m' => 18, 't' => 80.7,  'pc' => 46.5, 'w' => 10.2],
            ['m' => 24, 't' => 86.4,  'pc' => 47.2, 'w' => 11.5],
            ['m' => 30, 't' => 91.3,  'pc' => 48.2, 'w' => 12.5],
            ['m' => 36, 't' => 95.1,  'pc' => 48.6, 'w' => 13.9],
            ['m' => 42, 't' => 99.0,  'pc' => 49.0, 'w' => 14.9],
            ['m' => 48, 't' => 102.7, 'pc' => 49.3, 'w' => 15.9],
        ];

        $this->createControles($patient, $doctor, $dob, $controles);
    }

    private function seedThiago(Doctor $doctor): void
    {
        $dob = now()->subMonths(48)->startOfDay();

        $patient = Patient::updateOrCreate(
            ['full_name' => 'Thiago Méndez'],
            [
                'responsible_doctor_id' => $doctor->id,
                'date_of_birth' => $dob,
                'gender' => 'M',
                'birth_weight' => 3.3,
                'birth_height' => 49.9,
                'birth_head_circumference' => 34.5,
                'birth_type' => 'Normal',
                'birth_place' => 'Cochabamba',
                'blood_group' => 'A+',
            ]
        );

        $controles = [
            ['m' => 0,  't' => 49.9,  'pc' => 34.5, 'w' => 3.3],
            ['m' => 1,  't' => 54.7,  'pc' => 37.3, 'w' => 4.5],
            ['m' => 2,  't' => 58.4,  'pc' => 39.1, 'w' => 5.6],
            ['m' => 4,  't' => 63.9,  'pc' => 41.6, 'w' => 7.0],
            ['m' => 6,  't' => 67.6,  'pc' => 43.3, 'w' => 7.9],
            ['m' => 10, 't' => 73.3,  'pc' => 45.1, 'w' => 9.2],
            ['m' => 12, 't' => 75.7,  'pc' => 46.1, 'w' => 9.6],
            ['m' => 18, 't' => 82.3,  'pc' => 47.6, 'w' => 10.9],
            ['m' => 24, 't' => 87.8,  'pc' => 48.3, 'w' => 12.2],
            ['m' => 36, 't' => 96.1,  'pc' => 49.7, 'w' => 14.3],
            ['m' => 48, 't' => 103.3, 'pc' => 50.4, 'w' => 16.3],
        ];

        $this->createControles($patient, $doctor, $dob, $controles);
    }

    /**
     * Crea consultas con signos vitales para cada control pediátrico.
     *
     * @param  array<int, array{m: int, t: float, pc: float, w: float}>  $controles
     */
    private function createControles(Patient $patient, Doctor $doctor, \Carbon\CarbonInterface $dob, array $controles): void
    {
        // Limpiar consultas previas de este paciente (idempotencia)
        $existingIds = Consultation::where('patient_id', $patient->id)->pluck('id');
        VitalSign::whereIn('consultation_id', $existingIds)->delete();
        Consultation::where('patient_id', $patient->id)->forceDelete();

        foreach ($controles as $c) {
            $consultation = Consultation::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'type' => 'digital',
                'status' => 'finalized',
                'consultation_date' => $dob->copy()->addMonths($c['m']),
            ]);

            VitalSign::create([
                'consultation_id' => $consultation->id,
                'weight' => $c['w'],
                'height' => $c['t'],
                'head_circumference' => $c['pc'],
                'temperature' => 36.5,
            ]);
        }
    }
}
