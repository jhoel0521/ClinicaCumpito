<?php

use App\ValueObjects\ConsultationStatus;

describe('ConsultationStatus', function () {
    test('can create draft status', function () {
        $status = ConsultationStatus::make('draft');
        expect($status->value())->toBe('draft');
        expect($status->isDraft())->toBeTrue();
        expect($status->isSaved())->toBeFalse();
        expect($status->isFinalized())->toBeFalse();
    });

    test('can create saved status', function () {
        $status = ConsultationStatus::make('saved');
        expect($status->value())->toBe('saved');
        expect($status->isSaved())->toBeTrue();
    });

    test('can create finalized status', function () {
        $status = ConsultationStatus::make('finalized');
        expect($status->value())->toBe('finalized');
        expect($status->isFinalized())->toBeTrue();
    });

    test('throws on invalid status', function () {
        ConsultationStatus::make('pending');
    })->throws(\InvalidArgumentException::class);

    test('can compare statuses', function () {
        $saved1 = ConsultationStatus::make('saved');
        $saved2 = ConsultationStatus::make('saved');
        $draft = ConsultationStatus::make('draft');

        expect($saved1->equals($saved2))->toBeTrue();
        expect($saved1->equals($draft))->toBeFalse();
    });

    test('can convert to spanish string', function () {
        expect((string) ConsultationStatus::make('draft'))->toBe('Borrador');
        expect((string) ConsultationStatus::make('saved'))->toBe('Guardada');
        expect((string) ConsultationStatus::make('finalized'))->toBe('Finalizada');
    });
});
