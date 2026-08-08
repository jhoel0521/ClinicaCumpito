<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-07 10:00:00');
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
});

afterEach(function (): void {
    Carbon::setTestNow();
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

test('el header de consulta solo ofrece descartar borrador, sin botón de eliminar consulta', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();

    $draft = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
    ]);
    $saved = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);

    Livewire::actingAs($user)
        ->test('consultation-header', ['consultationId' => $draft->id])
        ->assertOk()
        ->assertSeeText('Descartar borrador')
        ->assertDontSee('dusk="delete-consultation"', false);

    Livewire::actingAs($user)
        ->test('consultation-header', ['consultationId' => $saved->id])
        ->assertOk()
        ->assertDontSeeText('Descartar borrador')
        ->assertDontSee('dusk="delete-consultation"', false);
});
