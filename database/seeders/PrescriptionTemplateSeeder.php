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
                'name' => 'Resfriado común (IRAA)',
                'description' => 'Tratamiento sintomático de infección respiratoria alta viral.',
                'items' => [
                    [
                        'med' => ['Paracetamol', 'Jarabe', '120 mg/5 ml'],
                        'dose' => '1 cucharadita (5 ml)',
                        'quantity' => '1 frasco 60 ml',
                        'frequency' => 'Cada 6 a 8 horas',
                        'duration' => '3 a 5 días',
                        'instructions' => 'Dar solo si tiene fiebre o dolor. No dar más de 4 veces por día.',
                    ],
                    [
                        'med' => ['Loratadina', 'Jarabe', '5 mg/5 ml'],
                        'dose' => '1 cucharadita (5 ml)',
                        'quantity' => '1 frasco 60 ml',
                        'frequency' => 'Una vez al día (por las noches)',
                        'duration' => '5 días',
                        'instructions' => 'Para nariz tapada y picazón. Menores de 2 años: consultar al médico.',
                    ],
                    [
                        'med' => ['Suero nasal', 'Solución nasal', '0.9%'],
                        'dose' => '2 a 3 gotas en cada fosa nasal',
                        'quantity' => '1 frasco',
                        'frequency' => 'Cada 4 a 6 horas',
                        'duration' => '5 a 7 días',
                        'instructions' => 'Limpiar las fosas nasales antes de aplicar. Inclinar la cabeza hacia atrás.',
                    ],
                ],
            ],

            // ── Vómitos / Gastroenteritis ─────────────────────────────────────
            [
                'name' => 'Vómitos y gastroenteritis aguda',
                'description' => 'Manejo de vómitos con rehidratación oral y antiemético.',
                'items' => [
                    [
                        'med' => ['Sales de Rehidratación Oral', 'Polvo', 'Sobre 200 ml'],
                        'dose' => 'Dar pequeños sorbos frecuentes',
                        'quantity' => '2 sobres',
                        'frequency' => 'A demanda (cada 5 a 10 minutos)',
                        'duration' => 'Hasta que cesen los síntomas',
                        'instructions' => 'Disolver 1 sobre en 200 ml de agua hervida fría. Dar cucharaditas pequeñas y seguidas para evitar más vómitos.',
                    ],
                    [
                        'med' => ['Ondansetrón', 'Solución oral', '4 mg/5 ml'],
                        'dose' => 'Según peso del niño (consultar al médico)',
                        'quantity' => '1 frasco 30 ml',
                        'frequency' => 'Cada 8 horas',
                        'duration' => '2 a 3 días',
                        'instructions' => 'Dar la primera dosis al inicio de los vómitos. Si no mejora tras 2 dosis, ir a urgencias.',
                    ],
                    [
                        'med' => ['Zinc', 'Jarabe', '10 mg/5 ml'],
                        'dose' => '1 cucharadita (5 ml)',
                        'quantity' => '1 frasco 60 ml',
                        'frequency' => 'Una vez al día',
                        'duration' => '10 a 14 días',
                        'instructions' => 'Comenzar junto con la rehidratación. Ayuda a reducir los días de diarrea.',
                    ],
                ],
            ],

            // ── Dengue (manejo ambulatorio) ───────────────────────────────────
            [
                'name' => 'Dengue sin signos de alarma (ambulatorio)',
                'description' => 'Manejo sintomático del dengue. SOLO Paracetamol. Ibuprofeno y Aspirina están contraindicados.',
                'items' => [
                    [
                        'med' => ['Paracetamol', 'Jarabe', '120 mg/5 ml'],
                        'dose' => '1 cucharadita (5 ml)',
                        'quantity' => '1 frasco 60 ml',
                        'frequency' => 'Cada 6 horas',
                        'duration' => 'Mientras tenga fiebre (máximo 7 días)',
                        'instructions' => 'NO dar Ibuprofeno ni Aspirina. Ir a urgencias si: fiebre de más de 3 días, dolor de barriga intenso, sangrado, vómitos que no paran o si la fiebre baja de golpe.',
                    ],
                    [
                        'med' => ['Sales de Rehidratación Oral', 'Polvo', 'Sobre 200 ml'],
                        'dose' => 'Dar sorbos frecuentes todo el día',
                        'quantity' => '4 sobres',
                        'frequency' => 'Durante todo el día',
                        'duration' => 'Mientras tenga fiebre',
                        'instructions' => 'Tomar mucho líquido es muy importante. El niño debe orinar al menos cada 6 horas.',
                    ],
                ],
            ],

            // ── Vitaminas generales ───────────────────────────────────────────
            [
                'name' => 'Suplementación vitamínica general',
                'description' => 'Suplementación para prevención de deficiencias nutricionales.',
                'items' => [
                    [
                        'med' => ['Vitamina C', 'Jarabe', '100 mg/5 ml'],
                        'dose' => '1 cucharadita (5 ml)',
                        'quantity' => '1 frasco 120 ml',
                        'frequency' => 'Una vez al día',
                        'duration' => '30 días',
                        'instructions' => 'Dar preferiblemente con el desayuno. Ayuda a absorber mejor el hierro.',
                    ],
                    [
                        'med' => ['Vitamina D3', 'Gotas', '400 UI/gota'],
                        'dose' => '1 a 2 gotas (según edad)',
                        'quantity' => '1 gotero',
                        'frequency' => 'Una vez al día',
                        'duration' => '3 meses',
                        'instructions' => 'Dar junto con una comida que tenga grasa para que se absorba mejor.',
                    ],
                    [
                        'med' => ['Sulfato ferroso', 'Jarabe', '25 mg/5 ml'],
                        'dose' => '1 cucharadita (5 ml)',
                        'quantity' => '1 frasco 120 ml',
                        'frequency' => 'Una vez al día en ayunas',
                        'duration' => '3 meses (reevaluar con análisis de sangre)',
                        'instructions' => 'Dar 30 minutos antes del desayuno. Las deposiciones pueden oscurecerse, eso es normal. No dar con leche.',
                    ],
                ],
            ],

            // ── Resfriado con componente bronquial ───────────────────────────
            [
                'name' => 'IRAA con broncoespasmo leve',
                'description' => 'Resfriado con sibilancias o dificultad respiratoria leve.',
                'items' => [
                    [
                        'med' => ['Salbutamol', 'Solución nebulización', '5 mg/ml'],
                        'dose' => 'Según peso del niño (indicar al médico)',
                        'quantity' => '1 frasco 10 ml',
                        'frequency' => 'Cada 20 minutos las primeras 3 veces, luego cada 4 a 6 horas',
                        'duration' => '3 a 5 días o hasta mejoría',
                        'instructions' => 'Nebulizar con 3 ml de suero fisiológico. Si no mejora tras 3 nebulizaciones, ir a urgencias.',
                    ],
                    [
                        'med' => ['Paracetamol', 'Jarabe', '120 mg/5 ml'],
                        'dose' => '1 cucharadita (5 ml)',
                        'quantity' => '1 frasco 60 ml',
                        'frequency' => 'Cada 6 a 8 horas',
                        'duration' => '3 a 5 días',
                        'instructions' => 'Dar solo si tiene fiebre o malestar general.',
                    ],
                    [
                        'med' => ['Suero nasal', 'Solución nasal', '0.9%'],
                        'dose' => '2 a 3 gotas en cada fosa nasal',
                        'quantity' => '1 frasco',
                        'frequency' => 'Cada 4 a 6 horas',
                        'duration' => '5 a 7 días',
                        'instructions' => 'Aplicar antes de cada nebulización y antes de dormir.',
                    ],
                ],
            ],
        ];

        foreach ($templates as $tplData) {
            $template = PrescriptionTemplate::firstOrCreate(
                ['name' => $tplData['name'], 'doctor_id' => $doctor->id],
                [
                    'description' => $tplData['description'],
                    'is_active' => true,
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
                    'template_id' => $template->id,
                    'medication_id' => $medication?->id,
                    'custom_medication_name' => $medication ? null : $medName,
                    'dose' => $item['dose'],
                    'quantity' => $item['quantity'],
                    'frequency' => $item['frequency'],
                    'duration' => $item['duration'],
                    'instructions' => $item['instructions'],
                ]);
            }
        }

        $this->command->info('✔ Templates de recetas: '.count($templates).' cargados (Resfriado, Vómitos, Dengue, Vitaminas, IRAA+broncoespasmo).');
    }
}
