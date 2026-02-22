<?php

use App\ValueObjects\BirthType;

describe('BirthType', function () {
    test('can create normal birth', function () {
        $type = BirthType::make('Normal');
        expect($type->value())->toBe('Normal');
        expect($type->isNormal())->toBeTrue();
        expect($type->isCesarean())->toBeFalse();
    });

    test('can create cesarean birth', function () {
        $type = BirthType::make('Cesarean');
        expect($type->value())->toBe('Cesarean');
        expect($type->isCesarean())->toBeTrue();
        expect($type->isNormal())->toBeFalse();
    });

    test('throws on invalid birth type', function () {
        BirthType::make('Vacuum');
    })->throws(\InvalidArgumentException::class);

    test('can compare birth types', function () {
        $normal1 = BirthType::make('Normal');
        $normal2 = BirthType::make('Normal');
        $cesarean = BirthType::make('Cesarean');

        expect($normal1->equals($normal2))->toBeTrue();
        expect($normal1->equals($cesarean))->toBeFalse();
    });

    test('can convert to spanish string', function () {
        expect((string) BirthType::make('Normal'))->toBe('Parto Natural');
        expect((string) BirthType::make('Cesarean'))->toBe('Parto por Cesárea');
    });
});
