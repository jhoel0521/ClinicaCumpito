<?php

namespace Tests\Feature\Catalogs;

use App\Models\Medication;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    if (Role::where('name', 'Admin')->doesntExist()) {
        Role::create(['name' => 'Admin']);
    }
});

test('admins can create medications', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $component = Livewire::test('pages::catalogs.medications')
        ->set('name', 'Paracetamol')
        ->set('genericName', 'Paracetamol')
        ->set('pharmaceuticalForm', 'Jarabe')
        ->set('concentration', '120mg/5ml')
        ->call('save');

    $component->assertHasNoErrors();
    $this->assertDatabaseHas('medications', [
        'name' => 'Paracetamol',
        'concentration' => '120mg/5ml',
    ]);
});

test('admins can update medications', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $medication = Medication::factory()->create(['name' => 'Old Name']);

    $component = Livewire::test('pages::catalogs.medications')
        ->call('edit', $medication->id)
        ->set('name', 'New Name Updated')
        ->call('save');

    $component->assertHasNoErrors();
    $this->assertDatabaseHas('medications', [
        'id' => $medication->id,
        'name' => 'New Name Updated',
    ]);
});
