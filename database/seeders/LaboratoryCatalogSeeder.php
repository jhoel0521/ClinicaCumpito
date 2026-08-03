<?php

namespace Database\Seeders;

use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryExamParameter;
use Illuminate\Database\Seeder;

class LaboratoryCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            [
                'name' => 'Hematología',
                'description' => 'Estudios de la sangre y sus componentes.',
                'exams' => [
                    [
                        'name' => 'Hemograma',
                        'description' => 'Biometría hemática completa: eritrograma, leucograma y plaquetas.',
                        'parameters' => [
                            'Leucocitos (WBC)', 'Eritrocitos (RBC)', 'Hemoglobina (Hgb)',
                            'Hematocrito (Hct)', 'Plaquetas', 'Neutrófilos %',
                            'Linfocitos %', 'Monocitos %', 'Eosinófilos %', 'Basófilos %',
                            'VCM (Volumen corpuscular medio)', 'HCM (Hemoglobina corpuscular media)',
                            'CHCM (Concentración HCM)',
                        ],
                    ],
                    [
                        'name' => 'Grupo Sanguíneo y Factor Rh',
                        'description' => 'Tipificación ABO y Rh.',
                        'parameters' => ['Grupo ABO', 'Factor Rh'],
                    ],
                    [
                        'name' => 'Tiempo de Protrombina (TP)',
                        'description' => 'Evaluación de la coagulación extrínseca.',
                        'parameters' => ['TP (segundos)', 'INR', 'Actividad %'],
                    ],
                    [
                        'name' => 'Tiempo de Tromboplastina Parcial (TTPA)',
                        'description' => 'Coagulación intrínseca.',
                        'parameters' => ['TTPA (segundos)'],
                    ],
                    [
                        'name' => 'VSG (Velocidad de Sedimentación)',
                        'description' => 'Marcador inespecífico de inflamación.',
                        'parameters' => ['VSG 1h (mm)'],
                    ],
                ],
            ],
            [
                'name' => 'Química Sanguínea',
                'description' => 'Análisis bioquímicos de la sangre.',
                'exams' => [
                    [
                        'name' => 'Glucosa',
                        'description' => 'Glucemia en ayunas y/o postprandial.',
                        'parameters' => ['Glucosa basal (mg/dL)', 'Glucosa postprandial 2h (mg/dL)'],
                    ],
                    [
                        'name' => 'Hemoglobina Glicosilada (HbA1c)',
                        'description' => 'Control glucémico a largo plazo.',
                        'parameters' => ['HbA1c %'],
                    ],
                    [
                        'name' => 'Perfil Lipídico',
                        'description' => 'Colesterol total, fracciones y triglicéridos.',
                        'parameters' => ['Colesterol Total (mg/dL)', 'HDL (mg/dL)', 'LDL (mg/dL)', 'Triglicéridos (mg/dL)', 'VLDL (mg/dL)'],
                    ],
                    [
                        'name' => 'Función Renal',
                        'description' => 'Evaluación del filtrado glomerular y metabolismo nitrogenado.',
                        'parameters' => ['Creatinina (mg/dL)', 'BUN/Urea (mg/dL)', 'Ácido Úrico (mg/dL)', 'Depuración de creatinina (mL/min)'],
                    ],
                    [
                        'name' => 'Función Hepática',
                        'description' => 'Enzimas y parámetros de síntesis hepática.',
                        'parameters' => ['ALT/TGP (U/L)', 'AST/TGO (U/L)', 'Fosfatasa Alcalina (U/L)', 'Bilirrubina Total (mg/dL)', 'Bilirrubina Directa (mg/dL)', 'Bilirrubina Indirecta (mg/dL)', 'Proteínas Totales (g/dL)', 'Albúmina (g/dL)', 'GGT (U/L)'],
                    ],
                    [
                        'name' => 'Perfil de Hierro',
                        'description' => 'Metabolismo del hierro.',
                        'parameters' => ['Hierro Sérico (µg/dL)', 'Ferritina (ng/mL)', 'TIBC (µg/dL)', 'Saturación de transferrina %'],
                    ],
                    [
                        'name' => 'Electrolitos',
                        'description' => 'Iones séricos.',
                        'parameters' => ['Sodio (mEq/L)', 'Potasio (mEq/L)', 'Cloro (mEq/L)', 'Calcio (mg/dL)', 'Fósforo (mg/dL)', 'Magnesio (mg/dL)'],
                    ],
                    [
                        'name' => 'TSH y Tiroides',
                        'description' => 'Función tiroidea.',
                        'parameters' => ['TSH (µUI/mL)', 'T4 libre (ng/dL)', 'T3 libre (pg/mL)'],
                    ],
                ],
            ],
            [
                'name' => 'Uroanálisis',
                'description' => 'Exámenes de orina.',
                'exams' => [
                    [
                        'name' => 'EGO (Examen General de Orina)',
                        'description' => 'Análisis físico, químico y microscópico de orina.',
                        'parameters' => [
                            'Aspecto', 'Color', 'Densidad', 'pH',
                            'Proteínas', 'Glucosa', 'Cetonas', 'Hemoglobina',
                            'Leucocitos', 'Nitritos', 'Bilirrubina', 'Urobilinógeno',
                            'Eritrocitos (microscopia)', 'Leucocitos (microscopia)',
                            'Células epiteliales', 'Cilindros', 'Bacterias', 'Cristales',
                        ],
                    ],
                    [
                        'name' => 'Urocultivo',
                        'description' => 'Cultivo de orina con antibiograma.',
                        'parameters' => ['Cultivo (UFC/mL)', 'Antibiograma'],
                    ],
                    [
                        'name' => 'Proteinuria 24h',
                        'description' => 'Proteínas en orina de 24 horas.',
                        'parameters' => ['Proteínas en orina 24h (mg/24h)'],
                    ],
                    [
                        'name' => 'Microalbuminuria',
                        'description' => 'Detección temprana de daño renal.',
                        'parameters' => ['Microalbúmina (mg/L)', 'Relación albúmina/creatinina'],
                    ],
                ],
            ],
            [
                'name' => 'Microbiología',
                'description' => 'Cultivos y antibiogramas.',
                'exams' => [
                    [
                        'name' => 'Hemocultivo',
                        'description' => 'Cultivo de sangre para detección de bacteriemia.',
                        'parameters' => ['Cultivo (muestra 1)', 'Cultivo (muestra 2)', 'Antibiograma'],
                    ],
                    [
                        'name' => 'Coprocultivo',
                        'description' => 'Cultivo de heces con antibiograma.',
                        'parameters' => ['Cultivo', 'Antibiograma'],
                    ],
                    [
                        'name' => 'Cultivo Faríngeo',
                        'description' => 'Cultivo de secreción faríngea (Streptococcus pyogenes).',
                        'parameters' => ['Cultivo', 'Antibiograma'],
                    ],
                    [
                        'name' => 'Test Streptococo A (rápido)',
                        'description' => 'Detección rápida de Streptococcus pyogenes grupo A.',
                        'parameters' => ['Resultado'],
                    ],
                ],
            ],
            [
                'name' => 'Parasitología',
                'description' => 'Exámenes para detección de parásitos.',
                'exams' => [
                    [
                        'name' => 'Coprológico (COPR)',
                        'description' => 'Examen general de heces, seriado x3 en días alternos.',
                        'parameters' => [
                            'Consistencia', 'Color', 'pH', 'Moco',
                            'Sangre oculta', 'Leucocitos fecales', 'Grasa neutral',
                            'Parásitos / Huevos', 'Levaduras',
                        ],
                    ],
                    [
                        'name' => 'Test de Graham (Oxiuros)',
                        'description' => 'Detección de Enterobius vermicularis.',
                        'parameters' => ['Resultado'],
                    ],
                    [
                        'name' => 'Gota Gruesa (Malaria)',
                        'description' => 'Plasmodium spp. — zonas endémicas.',
                        'parameters' => ['Resultado', 'Especie identificada', 'Parasitemia %'],
                    ],
                ],
            ],
            [
                'name' => 'Inmunología / Serología',
                'description' => 'Pruebas de anticuerpos, antígenos y marcadores infecciosos.',
                'exams' => [
                    [
                        'name' => 'Dengue',
                        'description' => 'Panel serológico para dengue.',
                        'parameters' => ['NS1 Antígeno (fase aguda días 1-5)', 'IgM (desde día 5)', 'IgG (inmunidad/infección tardía)', 'PCR Dengue'],
                    ],
                    [
                        'name' => 'VIH (ELISA)',
                        'description' => 'Tamizaje VIH de 4ª generación.',
                        'parameters' => ['Resultado ELISA 4ª gen'],
                    ],
                    [
                        'name' => 'VDRL (Sífilis)',
                        'description' => 'Tamizaje serológico de sífilis.',
                        'parameters' => ['Resultado', 'Título (si reactivo)'],
                    ],
                    [
                        'name' => 'Prueba de Embarazo (β-hCG)',
                        'description' => 'Hormona gonadotropina coriónica.',
                        'parameters' => ['Resultado cualitativo', 'β-hCG cuantitativa (mUI/mL)'],
                    ],
                    [
                        'name' => 'Chagas',
                        'description' => 'Tamizaje y confirmación de Trypanosoma cruzi.',
                        'parameters' => ['ELISA', 'IFI (título confirmatorio)'],
                    ],
                    [
                        'name' => 'PCR Cuantitativa (Proteína C Reactiva)',
                        'description' => 'Marcador de inflamación/infección bacteriana.',
                        'parameters' => ['PCR (mg/L)'],
                    ],
                    [
                        'name' => 'ASTO (Antiestreptolisina O)',
                        'description' => 'Infección estreptocócica previa.',
                        'parameters' => ['ASTO (UI/mL)'],
                    ],
                ],
            ],
            [
                'name' => 'Imagenología',
                'description' => 'Estudios de gabinete: radiología y ultrasonografía.',
                'exams' => [
                    [
                        'name' => 'Rayos X',
                        'description' => 'Radiología convencional.',
                        'parameters' => ['Tórax AP', 'Tórax AP y Lateral', 'Extremidad superior', 'Extremidad inferior', 'Columna cervical', 'Columna dorsal', 'Columna lumbar', 'Abdomen simple', 'Senos paranasales'],
                    ],
                    [
                        'name' => 'Ecografía',
                        'description' => 'Ultrasonografía diagnóstica.',
                        'parameters' => ['Abdominal', 'Abdominal y pélvica', 'Pélvica', 'Obstétrica', 'Renal', 'Testicular', 'Tiroides', 'Partes blandas'],
                    ],
                ],
            ],
        ];

        $totalExams = 0;
        $totalParams = 0;

        foreach ($catalog as $cat) {
            $category = LaboratoryCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['description' => $cat['description']]
            );

            foreach ($cat['exams'] as $examData) {
                $exam = LaboratoryExam::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $examData['name']],
                    ['description' => $examData['description']]
                );

                $totalExams++;

                foreach ($examData['parameters'] as $order => $paramName) {
                    LaboratoryExamParameter::firstOrCreate(
                        ['exam_id' => $exam->id, 'name' => $paramName],
                        ['sort_order' => $order]
                    );
                    $totalParams++;
                }
            }
        }

        $this->command->info('✔ Laboratorio: '.count($catalog)." categorías, {$totalExams} exámenes, {$totalParams} parámetros cargados.");
    }
}
