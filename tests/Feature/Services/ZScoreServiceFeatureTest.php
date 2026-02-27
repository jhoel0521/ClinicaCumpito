<?php

use App\Contracts\ZScoreServiceContract;
use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use Illuminate\Database\Eloquent\ModelNotFoundException;

describe('ZScoreServiceFeature', function () {
    test('lanza ModelNotFoundException si la grafica no tiene datos OMS asociados', function () {
        $grafica = OmsCatalogoGrafica::factory()->create();
        // No se crean OmsDatoGrafica para esta gráfica

        $service = app(ZScoreServiceContract::class);

        expect(fn () => $service->calculateByGrafica($grafica->id, 5.0, 10.0))
            ->toThrow(ModelNotFoundException::class);
    });

    test('calcula z-score usando contrato y datos OMS persistidos', function () {
        $grafica = OmsCatalogoGrafica::factory()->create();

        OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $grafica->id,
            'x_value' => 6.0,
            'l_value' => 0.1128,
            'm_value' => 7.934,
            's_value' => 0.1109,
        ]);

        $service = app(ZScoreServiceContract::class);
        $result = $service->calculateByGrafica(
            graficaId: $grafica->id,
            xValue: 6.0,
            measurement: 7.934,
        );

        expect($result->rounded(4))->toBe(0.0)
            ->and($result->isNormalRange())->toBeTrue()
            ->and($result->category())->toBe('Normal');
    });
});
