<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryItemResult;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\User;
use Livewire\Livewire;

test('la página detalle permite registrar resultados de un laboratorio pendiente', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $labRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    $item = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $labRequest->id,
        'exam_name' => 'Hemograma',
    ]);

    Livewire::actingAs($user)
        ->test('pages::laboratorios.detalle', ['laboratorio' => $labRequest->id])
        ->assertSeeText('Hemograma')
        ->set('newResults.'.$item->id.'.value', '12.5')
        ->set('newResults.'.$item->id.'.report', 'Normal')
        ->call('saveResult', $item->id)
        ->assertHasNoErrors();

    $result = LaboratoryItemResult::query()->sole();

    expect($result->value)->toBe('12.5')
        ->and($result->report_text)->toBe('Normal')
        ->and($result->consultation_id)->toBe($consultation->id)
        ->and($result->laboratory_request_item_id)->toBe($item->id);
});

test('la página detalle permite marcar como recibido tras guardar resultados', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $labRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    $item = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $labRequest->id,
    ]);
    LaboratoryItemResult::factory()->create([
        'laboratory_request_item_id' => $item->id,
        'consultation_id' => $consultation->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::laboratorios.detalle', ['laboratorio' => $labRequest->id])
        ->assertSeeText('Marcar como recibido')
        ->call('markReceived');

    expect($labRequest->fresh()->status)->toBe('received');
});

test('la página detalle permite eliminar un resultado dentro de la ventana de edición', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $labRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    $item = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $labRequest->id,
    ]);
    $result = LaboratoryItemResult::factory()->create([
        'laboratory_request_item_id' => $item->id,
        'consultation_id' => $consultation->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::laboratorios.detalle', ['laboratorio' => $labRequest->id])
        ->call('deleteResult', $result->id);

    expect(LaboratoryItemResult::find($result->id))->toBeNull();
});

test('la página detalle bloquea el borrado de resultados fuera de la ventana de 3 días', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $labRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
        'created_at' => now()->subDays(5),
    ]);
    $item = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $labRequest->id,
    ]);
    $result = LaboratoryItemResult::factory()->create([
        'laboratory_request_item_id' => $item->id,
        'consultation_id' => $consultation->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::laboratorios.detalle', ['laboratorio' => $labRequest->id])
        ->call('deleteResult', $result->id)
        ->assertSee('3 días');

    expect(LaboratoryItemResult::find($result->id))->not->toBeNull();
});

test('el historial de laboratorios del paciente ofrece "Registrar resultados" para pendientes', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $pending = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    $received = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'received',
    ]);
    LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $pending->id,
        'exam_name' => 'Coprológico',
    ]);
    LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $received->id,
        'exam_name' => 'Hemograma',
    ]);

    $response = $this->actingAs($user)
        ->get(route('pacientes.laboratorios', $patient))
        ->assertOk()
        ->assertSeeText('Registrar resultados')
        ->assertSee('dusk="register-results-'.$pending->id.'"', false)
        ->assertSeeText('Ver →');

    $response->assertDontSee('dusk="register-results-'.$received->id.'"');
});

test('la página detalle exige autenticación', function (): void {
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'finalized',
    ]);
    $labRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);

    $this->get(route('pacientes.laboratorios.show', [$patient, $labRequest]))
        ->assertRedirect(route('login'));
});
