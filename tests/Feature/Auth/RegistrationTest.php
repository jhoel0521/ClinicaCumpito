<?php

use Illuminate\Support\Facades\Route;

// El registro público está desactivado: los usuarios los crea soporte.

test('registration routes are not registered', function () {
    expect(Route::has('register'))->toBeFalse()
        ->and(Route::has('register.store'))->toBeFalse();
});

test('registration screen returns not found', function () {
    $this->get('/register')->assertNotFound();
});

test('guests cannot register through a direct post', function () {
    $this->post('/register', [
        'name' => 'Intruso',
        'email' => 'intruso@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
});
