<?php

use App\Models\Consultation;
use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use App\Models\Patient;
use App\Models\VitalSign;
use App\Services\GrowthChartService;
use App\Services\ZScoreService;

describe('GrowthChartService', function () {
    beforeEach(function () {
        $this->service = new GrowthChartService(new ZScoreService);

        // Gráfica de peso-para-talla (masculino) con datos OMS reales
        $this->grafica = OmsCatalogoGrafica::factory()->create([
            'codigo' => 'UNIT_TEST_WT_LEN_M',
            'nombre' => 'Test Peso/Talla Niños',
            'tipo_grafica' => 'peso_talla',
            'sexo' => 'M',
        ]);

        // Dos puntos LMS: talla 65 cm y talla 70 cm
        OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $this->grafica->id,
            'x_value' => 65.0,
            'l_value' => 0.3487,
            'm_value' => 7.5,
            's_value' => 0.11,
            'sd3neg' => 5.5, 'sd2neg' => 6.0, 'sd1neg' => 6.8,
            'sd0' => 7.5, 'sd1' => 8.2, 'sd2' => 9.0, 'sd3' => 9.8,
        ]);

        OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $this->grafica->id,
            'x_value' => 70.0,
            'l_value' => 0.3487,
            'm_value' => 8.5,
            's_value' => 0.11,
            'sd3neg' => 6.2, 'sd2neg' => 6.9, 'sd1neg' => 7.7,
            'sd0' => 8.5, 'sd1' => 9.3, 'sd2' => 10.2, 'sd3' => 11.1,
        ]);
    });

    test('getReferenceDatasets retorna estructura con labels y 7 datasets', function () {
        $result = $this->service->getReferenceDatasets($this->grafica->id);

        expect($result)->toHaveKeys(['labels', 'datasets'])
            ->and($result['labels'])->toBe([65.0, 70.0])
            ->and($result['datasets'])->toHaveCount(7);

        $labels = array_column($result['datasets'], 'label');
        expect($labels)->toBe(['-3 DS', '-2 DS', '-1 DS', 'Mediana', '+1 DS', '+2 DS', '+3 DS']);
    });

    test('getReferenceDatasets lanza excepción si la gráfica no tiene datos OMS', function () {
        $graficaSinDatos = OmsCatalogoGrafica::factory()->create([
            'codigo' => 'UNIT_TEST_EMPTY',
        ]);

        expect(fn () => $this->service->getReferenceDatasets($graficaSinDatos->id))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('getPatientDatapoints retorna array vacío si las consultas no tienen VitalSign', function () {
        $patient = Patient::factory()->create(['gender' => 'M']);

        Consultation::factory()->create([
            'patient_id' => $patient->id,
            // Sin VitalSign asociado
        ]);

        $result = $this->service->getPatientDatapoints($patient->id, $this->grafica->id);

        expect($result)->toBe([]);
    });

    test('getPatientDatapoints omite puntos donde la medición requerida es null', function () {
        $patient = Patient::factory()->create([
            'gender' => 'M',
            'date_of_birth' => now()->subMonths(6)->toDateString(),
        ]);

        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'consultation_date' => now(),
        ]);

        // VitalSign sin height → x_value null para tipo peso_talla
        VitalSign::factory()->create([
            'consultation_id' => $consultation->id,
            'height' => null,
            'weight' => 7.5,
        ]);

        $result = $this->service->getPatientDatapoints($patient->id, $this->grafica->id);

        expect($result)->toBe([]);
    });

    test('prepareChartData retorna estructura completa con las claves requeridas', function () {
        $patient = Patient::factory()->create(['gender' => 'M']);

        $result = $this->service->prepareChartData($patient->id, $this->grafica->id);

        expect($result)->toHaveKeys(['grafica', 'labels', 'reference_datasets', 'patient_datapoints'])
            ->and($result['grafica'])->toHaveKeys(['id', 'nombre', 'tipo_grafica'])
            ->and($result['grafica']['tipo_grafica'])->toBe('peso_talla')
            ->and($result['reference_datasets'])->toHaveCount(7)
            ->and($result['patient_datapoints'])->toBeArray();
    });

    it('retorna percentile_datasets con P3, P50 y P97', function () {
        $patient = Patient::factory()->create(['gender' => 'M']);

        $data = $this->service->prepareChartData($patient->id, $this->grafica->id);

        expect($data)->toHaveKey('percentile_datasets')
            ->and($data['percentile_datasets'])->toHaveCount(3)
            ->and($data['percentile_datasets'][0]['label'])->toContain('P3')
            ->and($data['percentile_datasets'][1]['label'])->toContain('P50')
            ->and($data['percentile_datasets'][2]['label'])->toContain('P97')
            ->and($data['percentile_datasets'][0]['dash'])->toBeTrue()
            ->and($data['percentile_datasets'][1]['dash'])->toBeFalse()
            ->and($data['percentile_datasets'][2]['dash'])->toBeTrue()
            ->and($data['percentile_datasets'][0]['data'])->toHaveCount(2)
            ->and($data['percentile_datasets'][1]['data'])->toHaveCount(2);
    });

    describe('guardas de rango (obs clienta: niño de 8 años graficado en "0 meses")', function () {
        beforeEach(function () {
            // Boleta peso_edad con rango OMS real: 0–60 meses
            $this->graficaEdad = OmsCatalogoGrafica::factory()->create([
                'codigo' => 'UNIT_TEST_WFA_M',
                'nombre' => 'Test Peso/Edad Niños',
                'tipo_grafica' => 'peso_edad',
                'sexo' => 'M',
            ]);

            OmsDatoGrafica::factory()->create([
                'oms_catalogo_grafica_id' => $this->graficaEdad->id,
                'x_value' => 0.0,
                'l_value' => 0.3487, 'm_value' => 3.3, 's_value' => 0.11,
            ]);
            OmsDatoGrafica::factory()->create([
                'oms_catalogo_grafica_id' => $this->graficaEdad->id,
                'x_value' => 60.0,
                'l_value' => 0.3487, 'm_value' => 18.0, 's_value' => 0.11,
            ]);
        });

        test('no grafica en 0 meses cuando el paciente no tiene fecha de nacimiento', function () {
            $patient = Patient::factory()->create([
                'gender' => 'M',
                'date_of_birth' => null,
            ]);

            $consultation = Consultation::factory()->create([
                'patient_id' => $patient->id,
                'consultation_date' => now(),
            ]);
            VitalSign::factory()->create([
                'consultation_id' => $consultation->id,
                'weight' => 35.0,
            ]);

            $result = $this->service->getPatientDatapoints($patient->id, $this->graficaEdad->id);

            expect($result)->toBe([]);
        });

        test('omite mediciones de un paciente de 8 años (fuera del rango 0-60 meses)', function () {
            $patient = Patient::factory()->create([
                'gender' => 'M',
                'date_of_birth' => now()->subYears(8)->toDateString(),
            ]);

            $consultation = Consultation::factory()->create([
                'patient_id' => $patient->id,
                'consultation_date' => now(),
            ]);
            VitalSign::factory()->create([
                'consultation_id' => $consultation->id,
                'weight' => 35.0,
            ]);

            $result = $this->service->getPatientDatapoints($patient->id, $this->graficaEdad->id);

            expect($result)->toBe([]);
        });

        test('sigue graficando mediciones dentro del rango de la boleta', function () {
            $patient = Patient::factory()->create([
                'gender' => 'M',
                'date_of_birth' => now()->subMonths(6)->toDateString(),
            ]);

            $consultation = Consultation::factory()->create([
                'patient_id' => $patient->id,
                'consultation_date' => now(),
            ]);
            VitalSign::factory()->create([
                'consultation_id' => $consultation->id,
                'weight' => 7.5,
            ]);

            $result = $this->service->getPatientDatapoints($patient->id, $this->graficaEdad->id);

            expect($result)->toHaveCount(1)
                ->and($result[0]['x'])->toBe(6.0)
                ->and($result[0]['y'])->toBe(7.5);
        });
    });
});
