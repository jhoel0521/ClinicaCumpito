<?php

namespace Tests\Feature\Catalogs;

use App\Models\LaboratoryCategory;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    if (Role::where('name', 'Admin')->doesntExist()) {
        Role::create(['name' => 'Admin']);
    }
});

test('catalogs index page is restricted to admins', function () {
    $user = User::factory()->create();
    // Non-admin user
    $this->actingAs($user)
        ->get(route('settings.catalogs'))
        ->assertForbidden();

    // Admin user
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('Admin');
    $this->actingAs($admin)
        ->get(route('settings.catalogs'))
        ->assertOk();
});

test('admins can create laboratory categories', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $component = Livewire::test('pages::catalogs.laboratories')
        ->set('categoryName', 'Microbiología')
        ->set('categoryDescription', 'Estudio de microorganismos')
        ->call('saveCategory');

    $component->assertHasNoErrors();
    $this->assertDatabaseHas('laboratory_categories', [
        'name' => 'Microbiología',
    ]);
});

test('admins can create laboratory exams', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $category = LaboratoryCategory::factory()->create();

    $component = Livewire::test('pages::catalogs.laboratories')
        ->set('examName', 'Cultivo de Orina')
        ->set('examCategoryId', $category->id)
        ->set('examUnit', 'UFC/ml')
        ->call('saveExam');

    $component->assertHasNoErrors();
    $this->assertDatabaseHas('laboratory_exams', [
        'name' => 'Cultivo de Orina',
        'category_id' => $category->id,
    ]);
});
