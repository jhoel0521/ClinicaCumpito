<?php

use Laravel\Fortify\Features;

test('the application timezone defaults to La Paz', function () {
    expect(config('app.timezone'))->toBe('America/La_Paz')
        ->and(now()->getTimezone()->getName())->toBe('America/La_Paz');
});

test('public registration and password reset are disabled', function () {
    expect(Features::enabled(Features::registration()))->toBeFalse()
        ->and(Features::enabled(Features::resetPasswords()))->toBeFalse();
});

test('livewire temporary uploads tolerate slow mobile connections', function () {
    expect(config('livewire.temporary_file_upload.max_upload_time'))->toBeGreaterThanOrEqual(30)
        ->and(config('livewire.temporary_file_upload.rules'))->toContain('max:20480')
        // El resto de la configuración de Livewire sigue viniendo del paquete.
        ->and(config('livewire.inject_assets'))->toBeTrue();
});

test('two factor authentication remains available', function () {
    expect(Features::enabled(Features::twoFactorAuthentication()))->toBeTrue();
});
