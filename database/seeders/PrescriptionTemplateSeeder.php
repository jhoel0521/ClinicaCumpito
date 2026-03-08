<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Medication;
use App\Models\PrescriptionTemplate;
use App\Models\PrescriptionTemplateItem;
use Illuminate\Database\Seeder;

class PrescriptionTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = Doctor::where('license_number', 'MP-001')->firstOrFail();

        $templates = [

            // ── Resfriado común ──────────────────────────────────────────────
            [
                'name'        => 'Resfriado común (IRAA)',
                'description' => 'Tratamiento sintomático de infección respiratoria alta viral.',
                'items' => [
                    [
                        'med'          => ['Paracetamol', 'Jarabe', '120 mg/5 ml'],
                        'dose'         => '10 - 15 mg/kg/dosis',
                        'frequency'    => 'Cada 6 - 8 horas',
                        'duration'     => '3 - 5 días',
                        'instructions' => 'Solo si fiebre > 38°C o dolor. No exceder 4 dosis/día.',
                    ],
                    [
                        'med'          => ['Loratadina', 'Jarabe', '5 mg/5 ml'],
                        'dose'         => '5 ml',
                        'frequency'    => 'Una vez al día (noche)',
                        'duration'     => '5 días',
                        'instructions' => 'Para síntomas nasales y alérgicos. Menores de 2 años: consultar dosis.',
                    ],
                    [
                        'med'          => ['Suero nasal', 'Solución nasal', '0.9%'],
                        'dose'         => '2 - 3 gotas en cada fosa nasal',
                        'frequency'    => 'Cada 4 - 6 horas',
                        'duration'     => '5 - 7 días',
                        'instructions' => 'Limpiar fosas nasales antes de aplicar. Incline la cabeza hacia atrás.',
                    ],
                ],
            ],

            // ── Vómitos / Gastroenteritis ─────────────────────────────────────
            [
                'name'        => 'Vómitos y gastroenteritis aguda',
                'description' => 'Manejo de vómitos con rehidratación oral y antiemético.',
                'items' => [
                    [
                        'med'          => ['Sales de Rehidratación Oral', 'Polvo', 'Sobre 200 ml'],
                        'dose'         => '5 - 10 ml/kg por cada episodio de vómito o diarrea',
                        'frequency'    => 'A demanda (cada 5 - 10 min pequeños sorbos)',
                        'duration'     => 'Hasta cese de síntomas',
                        'instructions' => 'Preparar 1 sobre en 200 ml de agua hervida fría. Dar cucharaditas frecuentes para evitar nuevos vómitos.',
                    ],
                    [
                        'med'          => ['Ondansetrón', 'Solución oral', '4 mg/5 ml'],
                        'dose'         => '0.15 mg/kg/dosis (máx. 4 mg)',
                        'frequency'    => 'Cada 8 horas',
                        'duration'     => '2 - 3 días',
                        'instructions' => 'Primera dosis al inicio. Si no ceden los vómitos tras 2 dosis, acudir al servicio de urgencias.',
                    ],
                    [
                        'med'          => ['Zinc', 'Jarabe', '10 mg/5 ml'],
                        'dose'         => '< 6 meses: 10 mg/día | ≥ 6 meses: 20 mg/día',
                        'frequency'    => 'Una vez al día',
                        'duration'     => '10 - 14 días',
                        'instructions' => 'Iniciar con la rehidratación. Reduce duración y severidad de diarrea.',
                    ],
                ],
            ],

            // ── Dengue (manejo ambulatorio) ───────────────────────────────────
            [
                'name'        => 'Dengue sin signos de alarma (ambulatorio)',
                'description' => 'Manejo sintomático del dengue. SOLO Paracetamol. Ibuprofeno y Aspirina están contraindicados.',
                'items' => [
                    [
                        'med'          => ['Paracetamol', 'Jarabe', '120 mg/5 ml'],
                        'dose'         => '10 - 15 mg/kg/dosis (máx. 1 g/dosis)',
                        'frequency'    => 'Cada 6 horas',
                        'duration'     => 'Mientras persista la fiebre (máx. 7 días)',
                        'instructions' => 'NO dar Ibuprofeno ni Aspirina. Acudir a urgencias si: fiebre > 3 días, dolor abdominal intenso, sangrado, vómitos persistentes o caída brusca de la fiebre.',
                    ],
                    [
                        'med'          => ['Sales de Rehidratación Oral', 'Polvo', 'Sobre 200 ml'],
                        'dose'         => '50 - 100 ml/kg/día en sorbos frecuentes',
                        'frequency'    => 'Durante todo el día',
                        'duration'     => 'Mientras dure la fiebre',
                        'instructions' => 'Hidratación oral agresiva es clave. Registrar diuresis: debe orinar al menos cada 6 horas.',
                    ],
                ],
            ],

            // ── Vitaminas generales ───────────────────────────────────────────
            [
                'name'        => 'Suplementación vitamínica general',
                'description' => 'Suplementación para prevención de deficiencias nutricionales.',
                'items' => [
                    [
                        'med'          => ['Vitamina C', 'Jarabe', '100 mg/5 ml'],
                        'dose'         => '5 ml',
                        'frequency'    => 'Una vez al día',
                        'duration'     => '30 días',
                        'instructions' => 'Preferiblemente con el desayuno. Ayuda a absorber el hierro.',
                    ],
                    [
                        'med'          => ['Vitamina D3', 'Gotas', '400 UI/gota'],
                        'dose'         => '< 1 año: 400 UI (1 gota) | 1 - 5 años: 600 UI (1-2 gotas)',
                        'frequency'    => 'Una vez al día',
                        'duration'     => '3 meses',
                        'instructions' => 'Dar con una comida que contenga grasa para mejor absorción.',
                    ],
                    [
                        'med'          => ['Sulfato ferroso', 'Jarabe', '25 mg/5 ml'],
                        'dose'         => '3 - 5 mg/kg/día de hierro elemental',
                        'frequency'    => 'Una vez al día en ayunas',
                        'duration'     => '3 meses (reevaluar con hemograma)',
                        'instructions' => 'Dar 30 minutos antes del desayuno. Puede causar deposiciones oscuras (normal). No dar con leche.',
                    ],
                ],
            ],

            // ── Resfriado con componente bronquial ───────────────────────────
            [
                'name'        => 'IRAA con broncoespasmo leve',
                'description' => 'Resfriado con sibilancias o dificultad respiratoria leve.',
                'items' => [
                    [
                        'med'          => ['Salbutamol', 'Solución nebulización', '5 mg/ml'],
                        'dose'         => '0.15 mg/kg (mínimo 2.5 mg, máximo 5 mg)',
                        'frequency'    => 'Cada 20 minutos las primeras 3 dosis, luego cada 4 - 6 horas',
                        'duration'     => '3 - 5 días o hasta mejoría',
                        'instructions' => 'Nebulizar con 3 ml de SF 0.9%. Si no mejora tras 3 nebulizaciones, ir a urgencias.',
                    ],
                    [
                        'med'          => ['Paracetamol', 'Jarabe', '120 mg/5 ml'],
                        'dose'         => '10 - 15 mg/kg/dosis',
                        'frequency'    => 'Cada 6 - 8 horas',
                        'duration'     => '3 - 5 días',
                        'instructions' => 'Solo si fiebre o malestar general.',
                    ],
                    [
                        'med'          => ['Suero nasal', 'Solución nasal', '0.9%'],
                        'dose'         => '2 - 3 gotas en cada fosa nasal',
                        'frequency'    => 'Cada 4 - 6 horas',
                        'duration'     => '5 - 7 días',
                        'instructions' => 'Antes de cada nebulización y antes de dormir.',
                    ],
                ],
            ],
        ];

        foreach ($templates as $tplData) {
            $template = PrescriptionTemplate::firstOrCreate(
                ['name' => $tplData['name'], 'doctor_id' => $doctor->id],
                [
                    'description' => $tplData['description'],
                    'is_active'   => true,
                ]
            );

            // Eliminar items previos para re-crearlos limpios (idempotente)
            $template->items()->delete();

            foreach ($tplData['items'] as $item) {
                [$medName, $form, $concentration] = $item['med'];

                $medication = Medication::where('name', $medName)
                    ->where('pharmaceutical_form', $form)
                    ->where('concentration', $concentration)
                    ->first();

                PrescriptionTemplateItem::create([
                    'template_id'          => $template->id,
                    'medication_id'        => $medication?->id,
                    'custom_medication_name' => $medication ? null : $medName,
                    'dose'                 => $item['dose'],
                    'frequency'            => $item['frequency'],
                    'duration'             => $item['duration'],
                    'instructions'         => $item['instructions'],
                ]);
            }
        }

        $this->command->info('✔ Templates de recetas: ' . count($templates) . ' cargados (Resfriado, Vómitos, Dengue, Vitaminas, IRAA+broncoespasmo).');
    }
}
