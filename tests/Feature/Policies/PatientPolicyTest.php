<?php

namespace Tests\Feature\Policies;

use App\Models\Patient;
use App\Models\User;

describe('PatientPolicy', function (): void {
    test('usuario autenticado puede ver lista de pacientes', function (): void {
        $this->actingAs(User::factory()->create())
            ->get(route('pacientes.index'))
            ->assertOk();
    });

    test('usuario autenticado puede ver dashboard de paciente', function (): void {
        $patient = Patient::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('pacientes.show', $patient->id))
            ->assertOk();
    });

    test('usuario autenticado puede ver formulario de creación', function (): void {
        $this->actingAs(User::factory()->create())
            ->get(route('pacientes.create'))
            ->assertOk();
    });

    test('usuario autenticado puede editar paciente', function (): void {
        $patient = Patient::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('pacientes.edit', $patient->id))
            ->assertOk();
    });

    test('usuario autenticado puede eliminar paciente', function (): void {
        $patient = Patient::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('pacientes.destroy', $patient->id))
            ->assertRedirect(route('pacientes.index'));

        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
    });

    test('usuario no autenticado es redirigido a login en todas las rutas', function (): void {
        $patient = Patient::factory()->create();

        $this->get(route('pacientes.index'))->assertRedirect(route('login'));
        $this->get(route('pacientes.show', $patient->id))->assertRedirect(route('login'));
        $this->get(route('pacientes.create'))->assertRedirect(route('login'));
    });
});
