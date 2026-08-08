<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-07 10:00:00');
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

test('el admin ve el botón de eliminar con fecha y control en la confirmación', function (): void {
    $admin = User::factory()->create()->assignRole('Admin');
    $doctor = Doctor::factory()->create();
    // Nació el 7 de enero de 2026 -> el 7 de agosto de 2026 tiene 7 meses
    $patient = Patient::factory()->create(['date_of_birth' => '2026-01-07']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:30:00',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('consultas.show', $consultation))
        ->assertOk();

    $response->assertSee('dusk="delete-consultation"', false);
    $response->assertSee('¿Eliminar la consulta del 07/08/2026 (control de los 7 meses)? Esta acción no se puede deshacer.');
});

test('el admin elimina una consulta guardada y deja de aparecer en el historial del paciente', function (): void {
    $admin = User::factory()->create()->assignRole('Admin');
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:30:00',
    ]);
    $labRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $labRequest->id]);

    $this->actingAs($admin)
        ->delete(route('consultas.destroy', $consultation))
        ->assertRedirect(route('consultas.index'));

    $this->assertSoftDeleted('consultations', ['id' => $consultation->id]);

    // Los laboratorios asociados no se destruyen físicamente (borrado lógico)
    $this->assertDatabaseHas('laboratory_requests', ['id' => $labRequest->id]);

    // El historial del paciente ya no muestra la consulta eliminada
    $this->actingAs($admin)
        ->get(route('pacientes.feed', $patient))
        ->assertOk()
        ->assertDontSeeText('Consulta médica');
});

test('una consulta finalizada no puede eliminarse', function (): void {
    $admin = User::factory()->create()->assignRole('Admin');
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $this->actingAs($admin)
        ->delete(route('consultas.destroy', $consultation))
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('consultations', ['id' => $consultation->id, 'deleted_at' => null]);
});

test('un doctor sin rol admin no puede eliminar consultas', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);

    $this->actingAs($user)
        ->delete(route('consultas.destroy', $consultation))
        ->assertForbidden();

    $this->assertDatabaseHas('consultations', ['id' => $consultation->id, 'deleted_at' => null]);
});

test('el header no muestra el botón de eliminar para quienes no pueden borrar', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);

    Livewire::actingAs($user)
        ->test('consultation-header', ['consultationId' => $consultation->id])
        ->assertOk()
        ->assertDontSee('dusk="delete-consultation"', false);
});
