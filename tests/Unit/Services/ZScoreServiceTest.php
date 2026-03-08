<?php

use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use App\Services\ZScoreService;

describe('ZScoreService', function () {
    beforeEach(function () {
        $this->service = new ZScoreService;
    });

    test('calculateFromLms retorna 0 cuando medición igual a M', function () {
        $result = $this->service->calculateFromLms(
            measurement: 7.934,
            lValue: 0.1128,
            mValue: 7.934,
            sValue: 0.1109,
        );

        expect($result->rounded(6))->toBe(0.0)
            ->and($result->category())->toBe('Normal');
    });

    test('calculateFromLms soporta rama L=0', function () {
        $result = $this->service->calculateFromLms(
            measurement: 11.0,
            lValue: 0.0,
            mValue: 10.0,
            sValue: 0.1,
        );

        expect($result->value())->toBeGreaterThan(0.0);
    });

    test('lanza excepción si la medición es cero o negativa', function () {
        expect(fn () => $this->service->calculateFromLms(0.0, 1.0, 10.0, 0.1))
            ->toThrow(\InvalidArgumentException::class, 'mayor a 0');

        expect(fn () => $this->service->calculateFromLms(-5.0, 1.0, 10.0, 0.1))
            ->toThrow(\InvalidArgumentException::class, 'mayor a 0');
    });

    test('lanza excepción si M o S son cero o negativos', function () {
        expect(fn () => $this->service->calculateFromLms(10.0, 1.0, 0.0, 0.1))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn () => $this->service->calculateFromLms(10.0, 1.0, 10.0, 0.0))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('retorna z-score negativo cuando medición es menor al valor M', function () {
        $zscore = $this->service->calculateFromLms(8.0, 1.0, 10.0, 0.1);

        expect($zscore->value())->toBeLessThan(0.0)
            ->and($zscore->isNormalRange())->toBeTrue();
    });

    test('calculateByGrafica usa el punto OMS más cercano por x_value', function () {
        $grafica = OmsCatalogoGrafica::factory()->create();

        OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $grafica->id,
            'x_value' => 3.0,
            'l_value' => 0.1,
            'm_value' => 6.5,
            's_value' => 0.1,
        ]);

        OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $grafica->id,
            'x_value' => 6.0,
            'l_value' => 0.1128,
            'm_value' => 7.934,
            's_value' => 0.1109,
        ]);

        $result = $this->service->calculateByGrafica(
            graficaId: $grafica->id,
            xValue: 5.8,
            measurement: 7.934,
        );

        expect($result->rounded(6))->toBe(0.0);
    });
});
