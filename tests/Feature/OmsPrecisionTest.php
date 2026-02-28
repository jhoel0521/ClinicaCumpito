<?php

/**
 * 7.4 — Pruebas de precisión OMS.
 *
 * Valida que el pipeline completo (WhoDataSeeder → ZScoreService → GrowthChartService)
 * reproduce los valores de referencia oficiales de la OMS.
 *
 * Estrategia:
 *   - Se siembran los datos OMS reales desde los archivos Excel oficiales.
 *   - Para cada tipo de gráfica se consulta un punto concreto (x_value conocido).
 *   - Se verifica:
 *       a) medición = M (mediana)  →  z-score ≈ 0
 *       b) medición = SD−2         →  z-score ≈ −2
 *       c) medición = SD+2         →  z-score ≈ +2
 *       d) medición entre SD lines →  z proporcional
 *   - Para el pipeline completo se crean pacientes de prueba con mediciones
 *     exactas y se valida la precisión del datapoint resultante.
 *
 * NOTA: Tests agrupados en métodos amplios para minimizar ejecuciones del
 * WhoDataSeeder (50 archivos Excel, ~1 GB en parsing acumulado).
 */

use App\Contracts\GrowthChartServiceContract;
use App\Contracts\ZScoreServiceContract;
use App\Models\Consultation;
use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use App\Models\Patient;
use App\Models\VitalSign;
use Database\Seeders\WhoDataSeeder;

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function graficaPorCodigo(string $codigo): OmsCatalogoGrafica
{
    return OmsCatalogoGrafica::where('codigo', $codigo)->firstOrFail();
}

function puntoOms(string $graficaId, float $xValue): OmsDatoGrafica
{
    return OmsDatoGrafica::where('oms_catalogo_grafica_id', $graficaId)
        ->where('x_value', $xValue)
        ->firstOrFail();
}

// ─────────────────────────────────────────────────────────────────────────────
// Suite
// ─────────────────────────────────────────────────────────────────────────────

describe('7.4 Precisión OMS', function () {

    beforeEach(function () {
        $this->seed(WhoDataSeeder::class);
        $this->zscore = app(ZScoreServiceContract::class);
        $this->chart = app(GrowthChartServiceContract::class);
    });

    // =====================================================================
    // A) ZScoreService — precisión LMS contra todas las líneas SD
    //    (mediana, SD±1, SD±2, SD±3) con datos OMS reales
    // =====================================================================

    test('ZScoreService: mediana produce z≈0 en las 10 boletas OMS', function () {
        $catalogs = [
            ['codigo' => 'peso_edad_M',         'x' => 6.0],
            ['codigo' => 'peso_edad_F',         'x' => 12.0],
            ['codigo' => 'talla_edad_M',        'x' => 0.0],
            ['codigo' => 'talla_edad_F',        'x' => 36.0],
            ['codigo' => 'perimetro_cefalico_M', 'x' => 3.0],
            ['codigo' => 'perimetro_cefalico_F', 'x' => 18.0],
            ['codigo' => 'imc_M',               'x' => 12.0],
            ['codigo' => 'imc_F',               'x' => 48.0],
            ['codigo' => 'peso_talla_M',        'x' => 70.0],
            ['codigo' => 'peso_talla_F',        'x' => 90.0],
        ];

        foreach ($catalogs as $cat) {
            $g = graficaPorCodigo($cat['codigo']);
            $punto = puntoOms($g->id, $cat['x']);

            $z = $this->zscore->calculateByGrafica($g->id, $cat['x'], (float) $punto->m_value);

            expect($z->rounded(1))->toBe(0.0,
                "Esperado z≈0 para mediana de {$cat['codigo']} x={$cat['x']}, obtenido z={$z->rounded(4)}"
            )->and($z->category())->toBe('Normal');
        }
    });

    test('ZScoreService: SD±2 produce z≈±2 y SD±3 produce z≈±3 con categorías correctas', function () {
        // SD−2 → z≈−2, categoría "Bajo"
        $g = graficaPorCodigo('peso_edad_F');
        $punto = puntoOms($g->id, 12.0);
        $z = $this->zscore->calculateByGrafica($g->id, 12.0, (float) $punto->sd2neg);
        expect($z->rounded(0))->toBe(-2.0)->and($z->category())->toBe('Bajo');

        // SD+2 → z≈+2, categoría "Alto"
        $g = graficaPorCodigo('peso_edad_M');
        $punto = puntoOms($g->id, 24.0);
        $z = $this->zscore->calculateByGrafica($g->id, 24.0, (float) $punto->sd2);
        expect($z->rounded(0))->toBe(2.0)->and($z->category())->toBe('Alto');

        // SD−3 → z≈−3 (la precisión flotante puede dar -2.999… → "Bajo")
        $g = graficaPorCodigo('talla_edad_M');
        $punto = puntoOms($g->id, 36.0);
        $z = $this->zscore->calculateByGrafica($g->id, 36.0, (float) $punto->sd3neg);
        expect($z->rounded(0))->toBe(-3.0)
            ->and($z->category())->toBeIn(['Bajo', 'Severamente bajo']);

        // SD+3 → z≈+3, categoría "Severamente alto"
        $g = graficaPorCodigo('perimetro_cefalico_F');
        $punto = puntoOms($g->id, 18.0);
        $z = $this->zscore->calculateByGrafica($g->id, 18.0, (float) $punto->sd3);
        expect($z->rounded(0))->toBe(3.0)->and($z->category())->toBe('Severamente alto');

        // SD−1 → z≈−1, categoría "Normal"
        $g = graficaPorCodigo('imc_F');
        $punto = puntoOms($g->id, 48.0);
        $z = $this->zscore->calculateByGrafica($g->id, 48.0, (float) $punto->sd1neg);
        expect($z->rounded(0))->toBe(-1.0)->and($z->category())->toBe('Normal');

        // SD+1 → z≈+1, categoría "Normal"
        $g = graficaPorCodigo('peso_talla_F');
        $punto = puntoOms($g->id, 90.0);
        $z = $this->zscore->calculateByGrafica($g->id, 90.0, (float) $punto->sd1);
        expect($z->rounded(0))->toBe(1.0)->and($z->category())->toBe('Normal');
    });

    test('ZScoreService: mediciones entre líneas SD producen z-score interpolado', function () {
        // Entre mediana y SD+1 → 0 < z < 1
        $g = graficaPorCodigo('peso_edad_M');
        $punto = puntoOms($g->id, 9.0);
        $midpoint = ((float) $punto->m_value + (float) $punto->sd1) / 2;
        $z = $this->zscore->calculateByGrafica($g->id, 9.0, $midpoint);
        expect($z->value())->toBeGreaterThan(0.0)
            ->and($z->value())->toBeLessThan(1.0)
            ->and($z->category())->toBe('Normal');

        // Entre SD−2 y SD−3 → −3 < z < −2
        $g = graficaPorCodigo('talla_edad_F');
        $punto = puntoOms($g->id, 24.0);
        $midpoint = ((float) $punto->sd2neg + (float) $punto->sd3neg) / 2;
        $z = $this->zscore->calculateByGrafica($g->id, 24.0, $midpoint);
        expect($z->value())->toBeLessThan(-2.0)
            ->and($z->value())->toBeGreaterThan(-3.0)
            ->and($z->category())->toBe('Bajo');

        // Entre SD+2 y SD+3 → 2 < z < 3
        $g = graficaPorCodigo('perimetro_cefalico_M');
        $punto = puntoOms($g->id, 12.0);
        $midpoint = ((float) $punto->sd2 + (float) $punto->sd3) / 2;
        $z = $this->zscore->calculateByGrafica($g->id, 12.0, $midpoint);
        expect($z->value())->toBeGreaterThan(2.0)
            ->and($z->value())->toBeLessThan(3.0)
            ->and($z->category())->toBe('Alto');
    });

    // =====================================================================
    // B) GrowthChartService — pipeline completo por tipo de gráfica
    // =====================================================================

    test('GrowthChartService: pipeline peso_edad y talla_edad con pacientes de prueba', function () {
        // ── peso_edad: niño 6m, peso = mediana → z≈0 ────────
        $g = graficaPorCodigo('peso_edad_M');
        $punto = puntoOms($g->id, 6.0);

        $patient = Patient::factory()->create([
            'gender' => 'M',
            'date_of_birth' => now()->subMonths(6)->toDateString(),
        ]);
        $c = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'consultation_date' => now(),
            'status' => 'draft',
        ]);
        VitalSign::factory()->create([
            'consultation_id' => $c->id,
            'weight' => (float) $punto->m_value,
            'height' => 67.0,
        ]);

        $data = $this->chart->prepareChartData($patient->id, $g->id);
        expect($data['patient_datapoints'])->toHaveCount(1);
        $dp = $data['patient_datapoints'][0];
        expect($dp['x'])->toBe(6.0)
            ->and($dp['y'])->toBe(round((float) $punto->m_value, 2))
            ->and($dp['z_score'])->toBe(0.0)
            ->and($dp['category'])->toBe('Normal');

        // ── talla_edad: niña 12m, talla = SD−2 → z≈−2 ──────
        $g2 = graficaPorCodigo('talla_edad_F');
        $punto2 = puntoOms($g2->id, 12.0);

        $patient2 = Patient::factory()->create([
            'gender' => 'F',
            'date_of_birth' => now()->subMonths(12)->toDateString(),
        ]);
        $c2 = Consultation::factory()->create([
            'patient_id' => $patient2->id,
            'consultation_date' => now(),
            'status' => 'draft',
        ]);
        VitalSign::factory()->create([
            'consultation_id' => $c2->id,
            'weight' => 8.0,
            'height' => (float) $punto2->sd2neg,
        ]);

        $data2 = $this->chart->prepareChartData($patient2->id, $g2->id);
        expect($data2['patient_datapoints'])->toHaveCount(1);
        $dp2 = $data2['patient_datapoints'][0];
        expect($dp2['x'])->toBe(12.0)
            ->and(round($dp2['z_score'], 0))->toBe(-2.0)
            ->and($dp2['category'])->toBeIn(['Bajo', 'Normal']);
    });

    test('GrowthChartService: pipeline peso_talla, perímetro cefálico e IMC', function () {
        // ── peso_talla: niño talla=80cm, peso=mediana → z≈0 ────
        $g = graficaPorCodigo('peso_talla_M');
        $punto = puntoOms($g->id, 80.0);

        $patient = Patient::factory()->create([
            'gender' => 'M',
            'date_of_birth' => now()->subMonths(12)->toDateString(),
        ]);
        $c = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'consultation_date' => now(),
            'status' => 'draft',
        ]);
        VitalSign::factory()->create([
            'consultation_id' => $c->id,
            'weight' => (float) $punto->m_value,
            'height' => 80.0,
        ]);

        $data = $this->chart->prepareChartData($patient->id, $g->id);
        expect($data['patient_datapoints'])->toHaveCount(1);
        expect($data['patient_datapoints'][0]['z_score'])->toBe(0.0)
            ->and($data['patient_datapoints'][0]['category'])->toBe('Normal');

        // ── perímetro cefálico: niña 9m, PC = SD+2 → z≈+2 ────
        $g2 = graficaPorCodigo('perimetro_cefalico_F');
        $punto2 = puntoOms($g2->id, 9.0);

        $patient2 = Patient::factory()->create([
            'gender' => 'F',
            'date_of_birth' => now()->subMonths(9)->toDateString(),
        ]);
        $c2 = Consultation::factory()->create([
            'patient_id' => $patient2->id,
            'consultation_date' => now(),
            'status' => 'draft',
        ]);
        VitalSign::factory()->create([
            'consultation_id' => $c2->id,
            'weight' => 8.0,
            'height' => 70.0,
            'head_circumference' => (float) $punto2->sd2,
        ]);

        $data2 = $this->chart->prepareChartData($patient2->id, $g2->id);
        expect($data2['patient_datapoints'])->toHaveCount(1);
        expect(round($data2['patient_datapoints'][0]['z_score'], 0))->toBe(2.0)
            ->and($data2['patient_datapoints'][0]['category'])->toBeIn(['Alto', 'Normal']);

        // ── imc: niño 24m, IMC=mediana → z≈0 ──────────────────
        $g3 = graficaPorCodigo('imc_M');
        $punto3 = puntoOms($g3->id, 24.0);
        $targetImc = (float) $punto3->m_value;
        $heightCm = 85.0;
        $weight = round($targetImc * (($heightCm / 100.0) ** 2), 2);

        $patient3 = Patient::factory()->create([
            'gender' => 'M',
            'date_of_birth' => now()->subMonths(24)->toDateString(),
        ]);
        $c3 = Consultation::factory()->create([
            'patient_id' => $patient3->id,
            'consultation_date' => now(),
            'status' => 'draft',
        ]);
        VitalSign::factory()->create([
            'consultation_id' => $c3->id,
            'weight' => $weight,
            'height' => $heightCm,
        ]);

        $data3 = $this->chart->prepareChartData($patient3->id, $g3->id);
        expect($data3['patient_datapoints'])->toHaveCount(1);
        expect(abs($data3['patient_datapoints'][0]['z_score']))->toBeLessThanOrEqual(0.1)
            ->and($data3['patient_datapoints'][0]['category'])->toBe('Normal');
    });

    test('GrowthChartService: múltiples consultas generan múltiples datapoints', function () {
        $g = graficaPorCodigo('peso_edad_F');
        $punto3 = puntoOms($g->id, 3.0);
        $punto6 = puntoOms($g->id, 6.0);

        $patient = Patient::factory()->create([
            'gender' => 'F',
            'date_of_birth' => now()->subMonths(6)->toDateString(),
        ]);

        // Consulta a los 3 meses — peso = mediana
        $c1 = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'consultation_date' => now()->subMonths(3),
            'status' => 'draft',
        ]);
        VitalSign::factory()->create([
            'consultation_id' => $c1->id,
            'weight' => (float) $punto3->m_value,
            'height' => 60.0,
        ]);

        // Consulta a los 6 meses — peso = mediana
        $c2 = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'consultation_date' => now(),
            'status' => 'draft',
        ]);
        VitalSign::factory()->create([
            'consultation_id' => $c2->id,
            'weight' => (float) $punto6->m_value,
            'height' => 67.0,
        ]);

        $data = $this->chart->prepareChartData($patient->id, $g->id);
        expect($data['patient_datapoints'])->toHaveCount(2);

        foreach ($data['patient_datapoints'] as $dp) {
            expect(abs($dp['z_score']))->toBeLessThanOrEqual(0.1)
                ->and($dp['category'])->toBe('Normal');
        }
    });

    // =====================================================================
    // C) Coherencia de curvas de referencia (SD ordering, SD0≈P50)
    // =====================================================================

    test('curvas de referencia: SD ordenadas, SD0≈P50, percentiles P3<P50<P97', function () {
        // ── SD0 ≈ P50 para peso_edad niño ────────────────────
        $g = graficaPorCodigo('peso_edad_M');
        $ref = $this->chart->getReferenceDatasets($g->id);

        $medianaData = collect($ref['datasets'])->firstWhere('label', 'Mediana')['data'];
        $p50Data = collect($ref['percentile_datasets'])
            ->firstWhere('label', 'OMS Ideal (P50)')['data'];

        foreach ($medianaData as $i => $sdVal) {
            if ($sdVal !== null && $p50Data[$i] !== null) {
                expect(abs($sdVal - $p50Data[$i]))->toBeLessThanOrEqual(0.15,
                    "Discrepancia SD0 vs P50 en índice {$i}: sd0={$sdVal}, p50={$p50Data[$i]}"
                );
            }
        }

        // ── SD−3 < SD−2 < … < SD+3 para talla_edad niño ─────
        $g2 = graficaPorCodigo('talla_edad_M');
        $ref2 = $this->chart->getReferenceDatasets($g2->id);

        $sdLabels = ['-3 DS', '-2 DS', '-1 DS', 'Mediana', '+1 DS', '+2 DS', '+3 DS'];
        $sdDatasets = [];
        foreach ($sdLabels as $label) {
            $sdDatasets[] = collect($ref2['datasets'])->firstWhere('label', $label)['data'];
        }

        $numPoints = count($ref2['labels']);
        for ($i = 0; $i < $numPoints; $i++) {
            for ($j = 0; $j < count($sdLabels) - 1; $j++) {
                if ($sdDatasets[$j][$i] !== null && $sdDatasets[$j + 1][$i] !== null) {
                    expect($sdDatasets[$j][$i])->toBeLessThan(
                        $sdDatasets[$j + 1][$i],
                        "En x={$ref2['labels'][$i]}: {$sdLabels[$j]} ({$sdDatasets[$j][$i]}) no es < {$sdLabels[$j + 1]} ({$sdDatasets[$j + 1][$i]})"
                    );
                }
            }
        }

        // ── P3 < P50 < P97 para peso_talla niña ─────────────
        $g3 = graficaPorCodigo('peso_talla_F');
        $ref3 = $this->chart->getReferenceDatasets($g3->id);

        $p3 = collect($ref3['percentile_datasets'])->first(fn ($d) => str_contains($d['label'], 'P3'))['data'];
        $p50 = collect($ref3['percentile_datasets'])->first(fn ($d) => str_contains($d['label'], 'P50'))['data'];
        $p97 = collect($ref3['percentile_datasets'])->first(fn ($d) => str_contains($d['label'], 'P97'))['data'];

        $numPoints3 = count($ref3['labels']);
        for ($i = 0; $i < $numPoints3; $i++) {
            if ($p3[$i] !== null && $p50[$i] !== null && $p97[$i] !== null) {
                expect($p3[$i])->toBeLessThan($p50[$i], "P3 ≥ P50 en x={$ref3['labels'][$i]}")
                    ->and($p50[$i])->toBeLessThan($p97[$i], "P50 ≥ P97 en x={$ref3['labels'][$i]}");
            }
        }
    });
});
