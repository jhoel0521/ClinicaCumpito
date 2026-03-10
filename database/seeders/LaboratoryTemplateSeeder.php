<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryTemplate;
use App\Models\LaboratoryTemplateItem;
use Illuminate\Database\Seeder;

class LaboratoryTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = Doctor::where('license_number', 'MP-001')->firstOrFail();

        $templates = [

            // ── Rutina Anual ──────────────────────────────────────────────────
            [
                'name' => 'Rutina Anual',
                'description' => 'Control de salud anual pediátrico estándar.',
                'exams' => [
                    'Hemograma Completo (BHC)',
                    'Glucemia en ayunas',
                    'Examen General de Orina (EGO)',
                    'Examen Coproparasitológico (seriado x3)',
                ],
            ],

            // ── Perfil Pre-Quirúrgico ─────────────────────────────────────────
            [
                'name' => 'Perfil Pre-Quirúrgico',
                'description' => 'Exámenes pre-operatorios obligatorios.',
                'exams' => [
                    'Hemograma Completo (BHC)',
                    'Tiempo de Protrombina (TP)',
                    'Tiempo de Tromboplastina Parcial (TTP)',
                    'Grupo Sanguíneo y Factor Rh',
                    'Glucemia en ayunas',
                    'Proteína C Reactiva (PCR) cuantitativa',
                    'Urea',
                    'Creatinina',
                ],
            ],

            // ── Control Chagas ────────────────────────────────────────────────
            [
                'name' => 'Tamizaje Chagas',
                'description' => 'Cribado de enfermedad de Chagas endémica en Bolivia.',
                'exams' => [
                    'Prueba de Chagas (ELISA)',
                    'Prueba de Chagas (IFI)',
                ],
            ],

            // ── Sospecha Dengue ───────────────────────────────────────────────
            [
                'name' => 'Sospecha de Dengue',
                'description' => 'Panel de dengue en fase aguda (primeros 5 días).',
                'exams' => [
                    'Hemograma Completo (BHC)',
                    'Dengue NS1 (Antígeno)',
                    'Dengue IgM / IgG',
                    'Proteína C Reactiva (PCR) cuantitativa',
                ],
            ],

            // ── Control Nutricional / Anemia ──────────────────────────────────
            [
                'name' => 'Control Nutricional y Anemia',
                'description' => 'Evaluación de estado nutricional e indicadores de anemia.',
                'exams' => [
                    'Hemograma Completo (BHC)',
                    'Ferritina',
                    'Hierro sérico',
                    'Proteínas Totales',
                    'Albúmina',
                ],
                'custom_exams' => [
                    ['name' => 'Vitamina D sérica (25-OH vitamina D)', 'indications' => 'En ayunas, preferentemente en la mañana.'],
                ],
            ],

            // ── Control Tiroides ──────────────────────────────────────────────
            [
                'name' => 'Control Tiroides',
                'description' => 'Tamizaje y seguimiento de disfunción tiroidea.',
                'exams' => [
                    'TSH',
                    'T4 libre (T4L)',
                ],
            ],

            // ── Perfil Lipídico ───────────────────────────────────────────────
            [
                'name' => 'Perfil Lipídico',
                'description' => 'Control de riesgo cardiovascular metabólico.',
                'exams' => [
                    'Colesterol Total',
                    'HDL Colesterol',
                    'LDL Colesterol',
                    'Triglicéridos',
                    'Glucemia en ayunas',
                ],
            ],

            // ── Infección Urinaria ────────────────────────────────────────────
            [
                'name' => 'Sospecha Infección Urinaria',
                'description' => 'Diagnóstico y antibiograma de ITU.',
                'exams' => [
                    'Examen General de Orina (EGO)',
                    'Urocultivo',
                ],
            ],

        ];

        foreach ($templates as $tplData) {
            $template = LaboratoryTemplate::firstOrCreate(
                ['name' => $tplData['name'], 'doctor_id' => $doctor->id],
                [
                    'description' => $tplData['description'],
                    'is_active' => true,
                ]
            );

            // Delete previous items to keep idempotent
            $template->items()->delete();

            // Add catalog exams
            foreach ($tplData['exams'] as $examName) {
                $exam = LaboratoryExam::where('name', $examName)->first();

                LaboratoryTemplateItem::create([
                    'template_id' => $template->id,
                    'laboratory_exam_id' => $exam?->id,
                    'custom_exam_name' => $exam ? null : $examName,
                    'indications' => null,
                ]);
            }

            // Add free-text (custom) exams
            foreach (isset($tplData['custom_exams']) ? $tplData['custom_exams'] : [] as $customExam) {
                LaboratoryTemplateItem::create([
                    'template_id' => $template->id,
                    'laboratory_exam_id' => null,
                    'custom_exam_name' => $customExam['name'],
                    'indications' => $customExam['indications'],
                ]);
            }
        }

        $this->command->info('✔ Plantillas de laboratorio: '.count($templates).' cargadas.');
    }
}
