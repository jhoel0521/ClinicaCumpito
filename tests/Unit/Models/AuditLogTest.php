<?php

namespace Tests\Unit\Models;

use App\Models\AuditLog;
use App\Models\Consultation;

describe('AuditLog (cast JSON)', function (): void {
    test('castea old_values y new_values a array', function (): void {
        $log = new AuditLog([
            'action' => 'updated',
            'auditable_type' => Consultation::class,
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => 'saved'],
        ]);

        expect($log->old_values)->toBe(['status' => 'draft'])
            ->and($log->new_values)->toBe(['status' => 'saved']);
    });

    test('tolera valores nulos en las columnas JSON', function (): void {
        $log = new AuditLog([
            'action' => 'created',
            'auditable_type' => Consultation::class,
        ]);

        expect($log->old_values)->toBeNull()
            ->and($log->new_values)->toBeNull();
    });
});
