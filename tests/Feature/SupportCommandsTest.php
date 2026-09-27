<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('crea un usuario con roles y perfil de doctor', function () {
    $this->artisan('soporte:crear-usuario', [
        'email' => 'Doctora@Clinica.com',
        'nombre' => 'Dra. Prueba',
        '--roles' => 'Admin,Doctor',
        '--doctor' => 'Dra. Prueba Pediatra',
        '--matricula' => 'MP-999',
    ])
        ->expectsQuestion('Contraseña (mín. 12, mayúsculas, números y símbolos)', 'ClaveSegura#2026')
        ->assertSuccessful();

    $user = User::where('email', 'doctora@clinica.com')->firstOrFail();

    expect($user->hasRole('Admin'))->toBeTrue()
        ->and($user->hasRole('Doctor'))->toBeTrue()
        ->and($user->doctor_id)->not->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('ClaveSegura#2026', $user->password))->toBeTrue();
});

it('rechaza una contraseña débil al crear usuario', function () {
    $this->artisan('soporte:crear-usuario', ['email' => 'x@clinica.com', 'nombre' => 'X'])
        ->expectsQuestion('Contraseña (mín. 12, mayúsculas, números y símbolos)', 'password')
        ->assertFailed();

    expect(User::where('email', 'x@clinica.com')->exists())->toBeFalse();
});

it('resetea la contraseña y opcionalmente quita el 2FA', function () {
    $user = User::factory()->withTwoFactor()->create(['email' => 'doc@clinica.com']);

    $this->artisan('soporte:resetear-password', ['email' => 'doc@clinica.com', '--quitar-2fa' => true])
        ->expectsQuestion('Nueva contraseña (mín. 12, mayúsculas, números y símbolos)', 'OtraClave#2026x')
        ->assertSuccessful();

    $user->refresh();

    expect(Hash::check('OtraClave#2026x', $user->password))->toBeTrue()
        ->and($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

it('informa si el usuario no existe', function () {
    $this->artisan('soporte:resetear-password', ['email' => 'nadie@clinica.com'])->assertFailed();
});
