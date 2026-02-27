<?php

use App\Contracts\GrowthChartServiceContract;
use App\Models\Consultation;
use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use App\Models\Patient;
use App\Models\VitalSign;

describe('GrowthChartServiceFeature', function () {
    test('prepareChartData retorna datos reales con consulta VitalSign y OMS persistidos', function () {
        // Gráfica OMS de peso-para-talla (masculino)
        $grafica = OmsCatalogoGrafica::factory()->create([
            'codigo' => 'FEAT_TEST_WT_LEN_M',
            'nombre' => 'Feature Test Peso/Talla',
            'tipo_grafica' => 'peso_talla',
            'sexo' => 'M',
        ]);

        // Punto OMS en x_value=65 (talla 65 cm → mediana = 7.5 kg)
        OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $grafica->id,
            'x_value' => 65.0,
            'l_value' => 0.3487,
            'm_value' => 7.5,
            's_value' => 0.11,
            'sd3neg' => 5.5, 'sd2neg' => 6.0, 'sd1neg' => 6.8,
            'sd0' => 7.5, 'sd1' => 8.2, 'sd2' => 9.0, 'sd3' => 9.8,
        ]);

        // Paciente masculino
        $patient = Patient::factory()->create([
            'gender' => 'M',
            'date_of_birth' => now()->subMonths(6)->toDateString(),
        ]);

        // Consulta con signos vitales: talla 65 cm y peso 7.5 kg (= mediana → Z-Score ≈ 0)
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'consultation_date' => now(),
        ]);

        VitalSign::factory()->create([
            'consultation_id' => $consultation->id,
            'weight' => 7.5,
            'height' => 65.0,
        ]);

        $service = app(GrowthChartServiceContract::class);
        $result = $service->prepareChartData($patient->id, $grafica->id);

        // Estructura completa
        expect($result)->toHaveKeys(['grafica', 'labels', 'reference_datasets', 'patient_datapoints'])
            // Metadatos de la gráfica
            ->and($result['grafica']['nombre'])->toBe('Feature Test Peso/Talla')
            ->and($result['grafica']['tipo_grafica'])->toBe('peso_talla')
            // Curvas de referencia
            ->and($result['labels'])->toBe([65.0])
            ->and($result['reference_datasets'])->toHaveCount(7)
            // Punto del paciente calculado
            ->and($result['patient_datapoints'])->toHaveCount(1)
            ->and($result['patient_datapoints'][0])->toHaveKeys(['x', 'y', 'z_score', 'category', 'date'])
            ->and($result['patient_datapoints'][0]['x'])->toBe(65.0)
            ->and($result['patient_datapoints'][0]['y'])->toBe(7.5)
            ->and($result['patient_datapoints'][0]['category'])->toBe('Normal');
    });
});
