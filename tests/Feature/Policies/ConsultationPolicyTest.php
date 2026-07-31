<?php

namespace Tests\Feature\Policies;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Tecnico', 'guard_name' => 'web']);
});

describe('ConsultationPolicy', function (): void {
    test('cualquier usuario autenticado puede listar consultas', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('consultas.index'))
            ->assertOk();
    });

    test('usuario sin doctor_id no puede iniciar consulta desde un paciente', function (): void {
        $user = User::factory()->create();
        $patient = \App\Models\Patient::factory()->create();

        $this->actingAs($user)
            ->post(route('consultas.quick-store', $patient))
            ->assertForbidden();
    });

    test('doctor puede iniciar consulta desde un paciente', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $patient = \App\Models\Patient::factory()->create();

        $this->actingAs($user)
            ->post(route('consultas.quick-store', $patient))
            ->assertRedirect();
    });

    test('admin puede iniciar consulta desde un paciente', function (): void {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        expect($user->can('create', Consultation::class))->toBeTrue();
    });

    test('doctor puede editar su propia consulta', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create(['doctor_id' => $doctor->id, 'status' => 'saved']);

        $this->actingAs($user)
            ->get(route('consultas.edit', $consultation->id))
            ->assertRedirect(route('consultas.show', $consultation->id));
    });

    test('doctor no puede editar consulta de otro doctor', function (): void {
        $doctor1 = Doctor::factory()->create();
        $doctor2 = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor1->id]);
        $consultation = Consultation::factory()->create(['doctor_id' => $doctor2->id]);

        $this->actingAs($user)
            ->get(route('consultas.edit', $consultation->id))
            ->assertForbidden();
    });

    test('usuario sin rol Admin no puede eliminar consulta', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create(['doctor_id' => $doctor->id]);

        $this->actingAs($user)
            ->delete(route('consultas.destroy', $consultation->id))
            ->assertForbidden();
    });

    test('Admin puede eliminar cualquier consulta', function (): void {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $consultation = Consultation::factory()->create(['status' => 'saved']);

        $this->actingAs($user)
            ->delete(route('consultas.destroy', $consultation->id))
            ->assertRedirect(route('consultas.index'));
    });

    test('tecnico puede iniciar consulta desde un paciente', function (): void {
        $user = User::factory()->create(['doctor_id' => null]);
        $user->assignRole('Tecnico');

        expect($user->can('create', Consultation::class))->toBeTrue();
    });

    test('tecnico puede editar consulta manual sin doctor asignado', function (): void {
        $user = User::factory()->create(['doctor_id' => null]);
        $user->assignRole('Tecnico');
        $consultation = Consultation::factory()->scanned()->create();

        $this->actingAs($user)
            ->get(route('consultas.edit', $consultation->id))
            ->assertRedirect(route('consultas.show', $consultation->id));
    });

    test('tecnico no puede editar consulta de un doctor', function (): void {
        $user = User::factory()->create(['doctor_id' => null]);
        $user->assignRole('Tecnico');
        $doctor = Doctor::factory()->create();
        $consultation = Consultation::factory()->create(['doctor_id' => $doctor->id]);

        $this->actingAs($user)
            ->get(route('consultas.edit', $consultation->id))
            ->assertForbidden();
    });

    test('admin puede editar cualquier consulta', function (): void {
        $user = User::factory()->create(['doctor_id' => null]);
        $user->assignRole('Admin');
        $consultation = Consultation::factory()->create(['status' => 'saved']);

        $this->actingAs($user)
            ->get(route('consultas.edit', $consultation->id))
            ->assertRedirect(route('consultas.show', $consultation->id));
    });
});
