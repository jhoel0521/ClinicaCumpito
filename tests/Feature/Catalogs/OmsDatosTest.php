<?php

namespace Tests\Feature\Catalogs;

use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
});

describe('OmsDatosTest', function () {
    test('solo Admin puede acceder a la vista de datos OMS', function () {
        $catalogo = OmsCatalogoGrafica::factory()->create();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $this->get("/catalogs/oms-datos/{$catalogo->id}")->assertForbidden();
    });

    test('admin puede crear un dato OMS', function () {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $catalogo = OmsCatalogoGrafica::factory()->create();

        Livewire::test('pages::catalogs.oms-datos', ['graficaId' => $catalogo->id])
            ->set('xValue', '3')
            ->set('lValue', '0.1738')
            ->set('mValue', '6.3762')
            ->set('sValue', '0.11727')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('oms_datos_graficas', [
            'oms_catalogo_grafica_id' => $catalogo->id,
        ]);

        $dato = OmsDatoGrafica::where('oms_catalogo_grafica_id', $catalogo->id)->first();
        expect((float) $dato->x_value)->toBe(3.0);
        expect((float) $dato->m_value)->toBe(6.3762);
    });

    test('admin puede editar un dato OMS', function () {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $catalogo = OmsCatalogoGrafica::factory()->create();
        $dato = OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $catalogo->id,
            'x_value' => 3.0,
            'l_value' => 0.1738,
            'm_value' => 6.3762,
            's_value' => 0.11727,
        ]);

        Livewire::test('pages::catalogs.oms-datos', ['graficaId' => $catalogo->id])
            ->call('edit', $dato->id)
            ->set('mValue', '6.5000')
            ->call('save')
            ->assertHasNoErrors();

        $dato->refresh();
        expect((float) $dato->m_value)->toBe(6.5);
    });

    test('admin puede eliminar un dato OMS (hard delete)', function () {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $catalogo = OmsCatalogoGrafica::factory()->create();
        $dato = OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $catalogo->id,
            'x_value' => 6.0,
            'l_value' => 0.1128,
            'm_value' => 7.934,
            's_value' => 0.1109,
        ]);

        Livewire::test('pages::catalogs.oms-datos', ['graficaId' => $catalogo->id])
            ->call('delete', $dato->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('oms_datos_graficas', ['id' => $dato->id]);
    });

    test('validaciones requeridas: xValue, lValue, mValue, sValue', function () {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $catalogo = OmsCatalogoGrafica::factory()->create();

        Livewire::test('pages::catalogs.oms-datos', ['graficaId' => $catalogo->id])
            ->set('xValue', '')
            ->set('lValue', '')
            ->set('mValue', '')
            ->set('sValue', '')
            ->call('save')
            ->assertHasErrors(['xValue', 'lValue', 'mValue', 'sValue']);
    });
});
