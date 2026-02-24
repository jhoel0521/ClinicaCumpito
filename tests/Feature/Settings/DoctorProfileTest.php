<?php

use App\Models\Doctor;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Asegurar que el rol Doctor exista para los tests
    if (Role::where('name', 'Doctor')->doesntExist()) {
        Role::create(['name' => 'Doctor']);
    }
});

test('doctor professional settings page can be rendered by doctors', function () {
    $user = User::factory()->create();
    $user->assignRole('Doctor');

    // Crear el registro de doctor vinculado
    Doctor::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('doctor-profile.edit'))
        ->assertOk()
        ->assertSee('Información Profesional');
});

test('doctor professional settings page is forbidden for non-doctors', function () {
    $user = User::factory()->create();
    // No le asignamos el rol Doctor

    $this->actingAs($user)
        ->get(route('doctor-profile.edit'))
        ->assertForbidden();
});

test('doctor professional information can be updated', function () {
    $user = User::factory()->create();
    $user->assignRole('Doctor');

    $doctor = Doctor::factory()->create([
        'user_id' => $user->id,
        'full_name' => 'Old Name',
        'specialty' => 'Old Specialty',
        'license_number' => '123456',
    ]);

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.doctor-profile')
        ->set('full_name', 'Dr. New Name')
        ->set('specialty', 'Pediatría')
        ->set('license_number', 'MT-777888')
        ->call('updateDoctorInformation');

    $response->assertHasNoErrors();
    $response->assertDispatched('doctor-profile-updated');

    $doctor->refresh();

    expect($doctor->full_name)->toBe('Dr. New Name');
    expect($doctor->specialty)->toBe('Pediatría');
    expect($doctor->license_number->value())->toBe('MT-777888');
});

test('doctor profile is created if it does not exist during update', function () {
    $user = User::factory()->create();
    $user->assignRole('Doctor');

    // NO creamos el registro de doctor inicialmente

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.doctor-profile')
        ->set('full_name', 'Dr. Auto Created')
        ->set('specialty', 'General')
        ->set('license_number', 'AC-112233')
        ->call('updateDoctorInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->doctor)->not->toBeNull();
    expect($user->doctor->full_name)->toBe('Dr. Auto Created');
    expect($user->doctor->license_number->value())->toBe('AC-112233');
});
