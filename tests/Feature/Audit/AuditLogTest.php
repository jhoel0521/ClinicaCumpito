<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\OmsCatalogoGrafica;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
});

describe('AuditLog', function (): void {
    test('se registra un log al crear una consulta', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);

        $this->actingAs($user);

        $consultation = Consultation::factory()->create(['doctor_id' => $doctor->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'auditable_type' => Consultation::class,
            'auditable_id' => $consultation->id,
            'user_id' => $user->id,
        ]);
    });

    test('se registra un log al actualizar una consulta', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);

        $this->actingAs($user);

        $consultation = Consultation::factory()->create(['doctor_id' => $doctor->id, 'status' => 'draft']);
        $consultation->update(['status' => 'saved']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'updated',
            'auditable_type' => Consultation::class,
            'auditable_id' => $consultation->id,
        ]);
    });

    test('se registra un log al eliminar una consulta', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);

        $this->actingAs($user);

        $consultation = Consultation::factory()->create(['doctor_id' => $doctor->id]);
        $consultation->delete();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'auditable_type' => Consultation::class,
            'auditable_id' => $consultation->id,
        ]);
    });

    test('se registra un log al crear una boleta OMS', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin);

        $grafica = OmsCatalogoGrafica::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'auditable_type' => OmsCatalogoGrafica::class,
            'auditable_id' => $grafica->id,
        ]);
    });

    test('el log guarda los valores anteriores en actualizaciones', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $this->actingAs($user);

        $consultation = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'status' => 'draft',
        ]);

        $consultation->update(['status' => 'saved']);

        $log = AuditLog::where('auditable_type', Consultation::class)
            ->where('auditable_id', $consultation->id)
            ->where('action', 'updated')
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->old_values)->toHaveKey('status')
            ->and($log->new_values)->toHaveKey('status');
    });

    test('AuditLog pertenece al usuario que realizó la acción', function (): void {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $this->actingAs($user);

        $consultation = Consultation::factory()->create(['doctor_id' => $doctor->id]);

        $log = AuditLog::where('auditable_type', Consultation::class)
            ->where('auditable_id', $consultation->id)
            ->where('action', 'created')
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->user->id)->toBe($user->id);
    });

    test('log sin usuario autenticado guarda user_id nulo', function (): void {
        $consultation = Consultation::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'auditable_type' => Consultation::class,
            'auditable_id' => $consultation->id,
            'user_id' => null,
        ]);
    });
});
