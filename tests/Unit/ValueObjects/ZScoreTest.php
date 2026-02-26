<?php

use App\ValueObjects\ZScore;

describe('ZScore', function () {
    test('crea zscore válido y redondea', function () {
        $zScore = ZScore::make(1.236);

        expect($zScore->value())->toBe(1.236)
            ->and($zScore->rounded())->toBe(1.24)
            ->and((string) $zScore)->toBe('1.24');
    });

    test('determina rango normal', function () {
        expect(ZScore::make(-2)->isNormalRange())->toBeTrue();
        expect(ZScore::make(2)->isNormalRange())->toBeTrue();
        expect(ZScore::make(2.1)->isNormalRange())->toBeFalse();
    });

    test('categoriza valores según umbrales clínicos', function () {
        expect(ZScore::make(-3.5)->category())->toBe('Severamente bajo');
        expect(ZScore::make(-2.5)->category())->toBe('Bajo');
        expect(ZScore::make(0)->category())->toBe('Normal');
        expect(ZScore::make(2.7)->category())->toBe('Alto');
        expect(ZScore::make(3.4)->category())->toBe('Severamente alto');
    });

    test('lanza excepción para valores no finitos', function () {
        ZScore::make(INF);
    })->throws(\InvalidArgumentException::class);
});
