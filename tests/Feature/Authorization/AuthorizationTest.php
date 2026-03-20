<?php

/**
 * 8.2 — Pruebas de autorización por rol/propietario.
 *
 * Verifica que las policies y gates del sistema denieguen o permitan
 * el acceso correcto según el rol y la propiedad del recurso.
 *
 * Cubre:
 *   - ConsultationPolicy: create (requiere doctor/Admin/Tecnico)
 *   - ConsultationPolicy: update (doctor solo edita la propia)
 *   - ConsultationPolicy: delete (solo Admin)
 *   - Gate manage-catalog (solo Admin)
 *   - PatientPolicy: unauthenticated redirige a login
 *   - Tecnico: puede crear consultas manuales, no digitales propias
 */

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Tecnico', 'guard_name' => 'web']);
});

// ─────────────────────────────────────────────────────────────────────────────
// ConsultationPolicy::create
// ─────────────────────────────────────────────────────────────────────────────

describe('8.2 — ConsultationPolicy::create', function (): void {
    test('usuario sin doctor_id y sin rol recibe 403 al crear consulta', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('consultas.create'))
            ->assertForbidden();
    });

    test('admin puede acceder al formulario de creación de consulta', function (): void {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $this->actingAs($user)
            ->get(route('consultas.create'))
            ->assertOk();
    });

    test('tecnico puede acceder al formulario de creación de consulta', function (): void {
        $user = User::factory()->create();
        $user->assignRole('Tecnico');

        $this->actingAs($user)
            ->get(route('consultas.create'))
            ->assertOk();
    });

    test('doctor (con doctor_id) puede acceder al formulario de creación', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);

        $this->actingAs($user)
            ->get(route('consultas.create'))
            ->assertOk();
    });

    test('usuario sin autenticar es redirigido al crear consulta', function (): void {
        $this->get(route('consultas.create'))
            ->assertRedirect(route('login'));
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// ConsultationPolicy::update (propietario)
// ─────────────────────────────────────────────────────────────────────────────

describe('8.2 — ConsultationPolicy::update (propietario)', function (): void {
    test('doctor puede ver formulario de edición de su propia consulta', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'status' => 'saved',
        ]);

        $this->actingAs($user)
            ->get(route('consultas.edit', $consultation->id))
            ->assertRedirect(route('consultas.show', $consultation->id));
    });

    test('doctor recibe 403 al intentar editar consulta de otro doctor', function (): void {
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();
        $userA = User::factory()->create(['doctor_id' => $doctorA->id]);
        $consultation = Consultation::factory()->create([
            'doctor_id' => $doctorB->id,
            'status' => 'saved',
        ]);

        $this->actingAs($userA)
            ->get(route('consultas.edit', $consultation->id))
            ->assertForbidden();
    });

    test('admin puede editar cualquier consulta', function (): void {
        $doctor = Doctor::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $consultation = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'status' => 'saved',
        ]);

        $this->actingAs($admin)
            ->get(route('consultas.edit', $consultation->id))
            ->assertRedirect(route('consultas.show', $consultation->id));
    });

    test('tecnico puede editar consulta manual sin doctor asignado', function (): void {
        $tecnico = User::factory()->create();
        $tecnico->assignRole('Tecnico');
        $consultation = Consultation::factory()->create([
            'doctor_id' => null,
            'type' => 'manual',
            'status' => 'saved',
        ]);

        $this->actingAs($tecnico)
            ->get(route('consultas.edit', $consultation->id))
            ->assertRedirect(route('consultas.show', $consultation->id));
    });

    test('tecnico recibe 403 al intentar editar consulta digital de un doctor', function (): void {
        $doctor = Doctor::factory()->create();
        $tecnico = User::factory()->create();
        $tecnico->assignRole('Tecnico');
        $consultation = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'type' => 'digital',
            'status' => 'saved',
        ]);

        $this->actingAs($tecnico)
            ->get(route('consultas.edit', $consultation->id))
            ->assertForbidden();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// ConsultationPolicy::delete (solo Admin)
// ─────────────────────────────────────────────────────────────────────────────

describe('8.2 — ConsultationPolicy::delete (solo Admin)', function (): void {
    test('doctor no puede eliminar una consulta', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create(['doctor_id' => $doctor->id]);

        $this->actingAs($user)
            ->delete(route('consultas.destroy', $consultation->id))
            ->assertForbidden();

        $this->assertDatabaseHas('consultations', ['id' => $consultation->id]);
    });

    test('usuario sin rol no puede eliminar una consulta', function (): void {
        $consultation = Consultation::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('consultas.destroy', $consultation->id))
            ->assertForbidden();
    });

    test('admin puede eliminar una consulta', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $consultation = Consultation::factory()->draft()->create();

        $this->actingAs($admin)
            ->delete(route('consultas.destroy', $consultation->id))
            ->assertRedirect(route('consultas.index'));
    });

    test('tecnico no puede eliminar una consulta', function (): void {
        $tecnico = User::factory()->create();
        $tecnico->assignRole('Tecnico');
        $consultation = Consultation::factory()->create();

        $this->actingAs($tecnico)
            ->delete(route('consultas.destroy', $consultation->id))
            ->assertForbidden();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Gate manage-catalog (solo Admin)
// ─────────────────────────────────────────────────────────────────────────────

describe('8.2 — Gate manage-catalog (solo Admin)', function (): void {
    test('admin tiene acceso al gate manage-catalog', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin);

        expect(auth()->user()->can('manage-catalog'))->toBeTrue();
    });

    test('doctor no tiene acceso al gate manage-catalog', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);

        $this->actingAs($user);

        expect(auth()->user()->can('manage-catalog'))->toBeFalse();
    });

    test('usuario sin rol no tiene acceso al gate manage-catalog', function (): void {
        $this->actingAs(User::factory()->create());

        expect(auth()->user()->can('manage-catalog'))->toBeFalse();
    });

    test('tecnico no tiene acceso al gate manage-catalog', function (): void {
        $tecnico = User::factory()->create();
        $tecnico->assignRole('Tecnico');

        $this->actingAs($tecnico);

        expect(auth()->user()->can('manage-catalog'))->toBeFalse();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// PatientPolicy — unauthenticated
// ─────────────────────────────────────────────────────────────────────────────

describe('8.2 — PatientPolicy: usuarios no autenticados', function (): void {
    test('usuario no autenticado es redirigido a login en todas las rutas de pacientes', function (): void {
        $patient = Patient::factory()->create();

        $this->get(route('pacientes.index'))->assertRedirect(route('login'));
        $this->get(route('pacientes.create'))->assertRedirect(route('login'));
        $this->get(route('pacientes.show', $patient->id))->assertRedirect(route('login'));
        $this->get(route('pacientes.edit', $patient->id))->assertRedirect(route('login'));
    });

    test('usuario no autenticado es redirigido a login en rutas de consultas', function (): void {
        $consultation = Consultation::factory()->create();

        $this->get(route('consultas.index'))->assertRedirect(route('login'));
        $this->get(route('consultas.create'))->assertRedirect(route('login'));
        $this->get(route('consultas.show', $consultation->id))->assertRedirect(route('login'));
    });

    test('usuario no autenticado es redirigido a login en rutas de catálogos', function (): void {
        $this->get(route('settings.catalogs'))->assertRedirect(route('login'));
        $this->get(route('settings.catalogs.laboratories'))->assertRedirect(route('login'));
        $this->get(route('settings.catalogs.medications'))->assertRedirect(route('login'));
        $this->get(route('settings.catalogs.vaccines'))->assertRedirect(route('login'));
    });
});
