<?php

use App\ValueObjects\ConsultationType;

describe('ConsultationType', function () {
    test('can create digital consultation', function () {
        $type = ConsultationType::make('digital');
        expect($type->value())->toBe('digital');
        expect($type->isDigital())->toBeTrue();
        expect($type->isManual())->toBeFalse();
    });

    test('can create manual consultation', function () {
        $type = ConsultationType::make('manual');
        expect($type->value())->toBe('manual');
        expect($type->isManual())->toBeTrue();
        expect($type->isDigital())->toBeFalse();
    });

    test('throws on invalid consultation type', function () {
        ConsultationType::make('phone');
    })->throws(\InvalidArgumentException::class);

    test('can compare consultation types', function () {
        $digital1 = ConsultationType::make('digital');
        $digital2 = ConsultationType::make('digital');
        $manual = ConsultationType::make('manual');

        expect($digital1->equals($digital2))->toBeTrue();
        expect($digital1->equals($manual))->toBeFalse();
    });

    test('can convert to spanish string', function () {
        expect((string) ConsultationType::make('digital'))->toBe('Consulta Digital');
        expect((string) ConsultationType::make('manual'))->toBe('Consulta Manual');
    });
});
