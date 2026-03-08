<?php

namespace Tests\Feature\Catalogs;

use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    if (Role::where('name', 'Admin')->doesntExist()) {
        Role::create(['name' => 'Admin']);
    }
});

test('admins can create vaccines', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $component = Livewire::test('pages::catalogs.vaccines')
        ->set('name', 'BCG')
        ->set('diseasePrevented', 'Tuberculosis')
        ->set('recommendedAge', 'Al nacer')
        ->set('doseSequence', 1)
        ->call('save');

    $component->assertHasNoErrors();
    $this->assertDatabaseHas('vaccines', [
        'name' => 'BCG',
        'dose_sequence' => 1,
    ]);
});
