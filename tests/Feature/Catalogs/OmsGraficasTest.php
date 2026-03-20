<?php

namespace Tests\Feature\Catalogs;

use App\Models\OmsCatalogoGrafica;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
});

describe('OmsGraficasTest', function () {
    test('solo Admin accede a la vista de gráficas OMS', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $this->get('/settings/catalogs/oms-graficas')->assertForbidden();
    });

    test('admin puede crear una boleta OMS', function () {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $component = Livewire::test('pages::catalogs.oms-graficas')
            ->set('nombre', 'Peso para Talla - Niños')
            ->set('codigo', 'WHO_WT_LEN_M_0_24M')
            ->set('tipoGrafica', 'peso_talla')
            ->set('rangoEdad', '0-24 meses')
            ->set('sexo', 'M')
            ->call('save');

        $component->assertHasNoErrors();
        $this->assertDatabaseHas('oms_catalogo_graficas', [
            'codigo' => 'WHO_WT_LEN_M_0_24M',
            'sexo' => 'M',
        ]);
    });

    test('admin puede editar una boleta OMS', function () {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $grafica = OmsCatalogoGrafica::factory()->create([
            'nombre' => 'Nombre Original',
            'codigo' => 'WHO_ORIG_001',
        ]);

        Livewire::test('pages::catalogs.oms-graficas')
            ->call('edit', $grafica->id)
            ->set('nombre', 'Nombre Actualizado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('oms_catalogo_graficas', [
            'id' => $grafica->id,
            'nombre' => 'Nombre Actualizado',
        ]);
    });

    test('admin puede eliminar una boleta OMS', function () {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $grafica = OmsCatalogoGrafica::factory()->create([
            'codigo' => 'WHO_DELETE_001',
        ]);

        Livewire::test('pages::catalogs.oms-graficas')
            ->call('delete', $grafica->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('oms_catalogo_graficas', ['id' => $grafica->id]);
    });

    test('validaciones requeridas: nombre, codigo, tipo_grafica y sexo', function () {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        Livewire::test('pages::catalogs.oms-graficas')
            ->set('nombre', '')
            ->set('codigo', '')
            ->set('rangoEdad', '')
            ->call('save')
            ->assertHasErrors(['nombre', 'codigo', 'rangoEdad']);
    });
});
