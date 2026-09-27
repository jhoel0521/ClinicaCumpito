<?php

use Illuminate\Support\Facades\Route;

// El reseteo de contraseña por correo está desactivado: lo gestiona soporte.

test('password reset routes are not registered', function () {
    expect(Route::has('password.request'))->toBeFalse()
        ->and(Route::has('password.reset'))->toBeFalse()
        ->and(Route::has('password.update'))->toBeFalse();
});

test('forgot password url returns not found', function () {
    $this->get('/forgot-password')->assertNotFound();
});

test('login screen shows support hint instead of reset link', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('data-test="password-support-hint"', false)
        ->assertDontSee('/forgot-password', false);
});
