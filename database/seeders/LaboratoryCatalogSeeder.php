<?php

namespace Database\Seeders;

use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
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
                    ['name' => 'Hemograma Completo (BHC)',  'unit' => 'Ver reporte',  'reference_range' => 'Ver reporte', 'description' => 'Eritrograma, leucograma y plaquetas.'],
                    ['name' => 'Velocidad de Sedimentación Globular (VSG)', 'unit' => 'mm/h', 'reference_range' => '< 20',          'description' => 'Marcador inespecífico de inflamación.'],
                    ['name' => 'Grupo Sanguíneo y Factor Rh', 'unit' => 'N/A',       'reference_range' => 'N/A',           'description' => 'Tipificación ABO y Rh.'],
                    ['name' => 'Tiempo de Protrombina (TP)', 'unit' => 'segundos',   'reference_range' => '11 - 13.5',     'description' => 'Evaluación de la coagulación extrínseca.'],
                    ['name' => 'Tiempo de Tromboplastina Parcial (TTP)', 'unit' => 'segundos', 'reference_range' => '25 - 35', 'description' => 'Coagulación intrínseca.'],
                ],
            ],
            [
                'name' => 'Química Sanguínea',
                'description' => 'Análisis bioquímicos de la sangre.',
                'exams' => [
                    ['name' => 'Glucemia en ayunas',       'unit' => 'mg/dL',  'reference_range' => '70 - 100',     'description' => ''],
                    ['name' => 'Glucemia postprandial (2h)', 'unit' => 'mg/dL', 'reference_range' => '< 140',        'description' => ''],
                    ['name' => 'Urea',                     'unit' => 'mg/dL',  'reference_range' => '15 - 40',      'description' => ''],
                    ['name' => 'Creatinina',               'unit' => 'mg/dL',  'reference_range' => '0.3 - 0.7',   'description' => 'Ajustar por edad.'],
                    ['name' => 'Ácido úrico',              'unit' => 'mg/dL',  'reference_range' => '2.0 - 5.5',   'description' => ''],
                    ['name' => 'ALT (TGP)',                'unit' => 'U/L',    'reference_range' => '< 35',         'description' => 'Enzima hepática.'],
                    ['name' => 'AST (TGO)',                'unit' => 'U/L',    'reference_range' => '< 40',         'description' => 'Enzima hepática.'],
                    ['name' => 'Bilirrubina Total',        'unit' => 'mg/dL',  'reference_range' => '0.2 - 1.0',   'description' => ''],
                    ['name' => 'Bilirrubina Directa',      'unit' => 'mg/dL',  'reference_range' => '0.0 - 0.3',   'description' => ''],
                    ['name' => 'Proteínas Totales',        'unit' => 'g/dL',   'reference_range' => '6.0 - 8.0',   'description' => ''],
                    ['name' => 'Albúmina',                 'unit' => 'g/dL',   'reference_range' => '3.5 - 5.0',   'description' => ''],
                    ['name' => 'Colesterol Total',         'unit' => 'mg/dL',  'reference_range' => '< 170',        'description' => 'Pediátrico.'],
                    ['name' => 'HDL Colesterol',           'unit' => 'mg/dL',  'reference_range' => '> 45',         'description' => ''],
                    ['name' => 'LDL Colesterol',           'unit' => 'mg/dL',  'reference_range' => '< 110',        'description' => ''],
                    ['name' => 'Triglicéridos',            'unit' => 'mg/dL',  'reference_range' => '< 150',        'description' => ''],
                    ['name' => 'Calcio sérico',            'unit' => 'mg/dL',  'reference_range' => '8.5 - 10.5',  'description' => ''],
                    ['name' => 'Fósforo sérico',           'unit' => 'mg/dL',  'reference_range' => '3.5 - 5.5',   'description' => ''],
                    ['name' => 'Hierro sérico',            'unit' => 'µg/dL',  'reference_range' => '50 - 120',     'description' => ''],
                    ['name' => 'Ferritina',                'unit' => 'ng/mL',  'reference_range' => '12 - 150',     'description' => ''],
                ],
            ],
            [
                'name' => 'Inmunología y Marcadores Infecciosos',
                'description' => 'Pruebas de respuesta inmune e infección.',
                'exams' => [
                    ['name' => 'Proteína C Reactiva (PCR) cuantitativa', 'unit' => 'mg/L', 'reference_range' => '< 5',  'description' => 'Marcador de infección bacteriana.'],
                    ['name' => 'Procalcitonina (PCT)',  'unit' => 'ng/mL', 'reference_range' => '< 0.5',  'description' => 'Sepsis bacteriana.'],
                    ['name' => 'Antiestreptolisina O (ASTO)', 'unit' => 'UI/mL', 'reference_range' => '< 200', 'description' => 'Infección estreptocócica previa.'],
                    ['name' => 'IgE Total',             'unit' => 'UI/mL', 'reference_range' => 'Según edad', 'description' => 'Atopia y alergia.'],
                    ['name' => 'TSH',                   'unit' => 'µUI/mL', 'reference_range' => '0.5 - 4.5', 'description' => 'Función tiroidea.'],
                    ['name' => 'T4 libre (T4L)',        'unit' => 'ng/dL', 'reference_range' => '0.8 - 1.8', 'description' => ''],
                    ['name' => 'Prueba de Chagas (ELISA)', 'unit' => 'N/A', 'reference_range' => 'Negativo',  'description' => 'Endémica Bolivia. Confirmar con IFI.'],
                    ['name' => 'Prueba de Chagas (IFI)', 'unit' => 'N/A', 'reference_range' => '< 1:32',    'description' => 'Confirmación de Chagas.'],
                    ['name' => 'Dengue NS1 (Antígeno)', 'unit' => 'N/A',  'reference_range' => 'Negativo',  'description' => 'Fase aguda dengue (días 1-5).'],
                    ['name' => 'Dengue IgM / IgG',      'unit' => 'N/A',  'reference_range' => 'Negativo',  'description' => 'Dengue tardío o inmunidad.'],
                    ['name' => 'PPD (Mantoux)',          'unit' => 'mm',   'reference_range' => '< 10 mm',   'description' => 'Tamizaje tuberculosis.'],
                    ['name' => 'HIV (Elisa 4ta gen)',    'unit' => 'N/A',  'reference_range' => 'No reactivo', 'description' => ''],
                    ['name' => 'VDRL',                  'unit' => 'N/A',  'reference_range' => 'No reactivo', 'description' => 'Tamizaje sífilis.'],
                ],
            ],
            [
                'name' => 'Uroanálisis',
                'description' => 'Exámenes de orina.',
                'exams' => [
                    ['name' => 'Examen General de Orina (EGO)', 'unit' => 'N/A',    'reference_range' => 'Normal',    'description' => 'Físico, químico y microscópico.'],
                    ['name' => 'Urocultivo',                    'unit' => 'UFC/ml', 'reference_range' => '< 10.000',  'description' => 'Cultivo + antibiograma.'],
                    ['name' => 'Proteinuria en orina de 24h',   'unit' => 'mg/24h', 'reference_range' => '< 150',     'description' => 'Síndrome nefrótico si > 40 mg/m²/h.'],
                    ['name' => 'Microalbuminuria',              'unit' => 'mg/L',   'reference_range' => '< 30',      'description' => ''],
                ],
            ],
            [
                'name' => 'Microbiología',
                'description' => 'Cultivos y antibiogramas.',
                'exams' => [
                    ['name' => 'Hemocultivo (x2)',               'unit' => 'N/A', 'reference_range' => 'Sin crecimiento', 'description' => 'Dos extracciones en sitios diferentes.'],
                    ['name' => 'Coprocultivo',                   'unit' => 'N/A', 'reference_range' => 'Sin crecimiento', 'description' => ''],
                    ['name' => 'Cultivo de secreción faríngea',  'unit' => 'N/A', 'reference_range' => 'Sin crecimiento', 'description' => 'Streptococcus pyogenes.'],
                    ['name' => 'Test rápido Streptococo A',      'unit' => 'N/A', 'reference_range' => 'Negativo',        'description' => ''],
                ],
            ],
            [
                'name' => 'Parasitología',
                'description' => 'Exámenes para detección de parásitos.',
                'exams' => [
                    ['name' => 'Examen Coproparasitológico (seriado x3)', 'unit' => 'N/A', 'reference_range' => 'Negativo', 'description' => 'Tres muestras en días alternos.'],
                    ['name' => 'Test de Graham (Oxiuros)',                'unit' => 'N/A', 'reference_range' => 'Negativo', 'description' => 'Enterobius vermicularis.'],
                    ['name' => 'Gota gruesa (Malaria)',                  'unit' => 'N/A', 'reference_range' => 'Negativo', 'description' => 'Plasmodium spp. Zonas endémicas Bolivia.'],
                ],
            ],
        ];

        foreach ($catalog as $cat) {
            $category = LaboratoryCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['description' => $cat['description']]
            );

            foreach ($cat['exams'] as $exam) {
                LaboratoryExam::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $exam['name']],
                    [
                        'unit' => $exam['unit'],
                        'reference_range' => $exam['reference_range'],
                        'description' => $exam['description'],
                    ]
                );
            }
        }

        $total = collect($catalog)->sum(fn ($c) => count($c['exams']));
        $this->command->info('✔ Laboratorio: '.count($catalog)." categorías, {$total} exámenes cargados.");
    }
}
