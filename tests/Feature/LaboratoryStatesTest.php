<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryItemResult;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\User;
use Livewire\Livewire;

test('el detalle permite editar un resultado existente en una orden pendiente', function (): void {
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
        'value' => '10.0',
        'is_abnormal' => false,
    ]);

    Livewire::actingAs($user)
        ->test('pages::laboratorios.detalle', ['laboratorio' => $labRequest->id])
        ->set('editResults.'.$result->id.'.value', '11.5')
        ->set('editResults.'.$result->id.'.report', 'Revisado por el médico')
        ->set('editResults.'.$result->id.'.abnormal', true)
        ->call('updateResult', $result->id)
        ->assertHasNoErrors();

    expect($result->fresh()->value)->toBe('11.5')
        ->and($result->fresh()->report_text)->toBe('Revisado por el médico')
        ->and($result->fresh()->is_abnormal)->toBeTrue();
});

test('el detalle muestra la fecha de registro de cada resultado', function (): void {
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
        'created_at' => '2026-08-06 15:30:00',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.laboratorios.show', [$patient, $labRequest]))
        ->assertOk()
        ->assertSeeText('Registrado el')
        ->assertSeeText('06/08/2026 15:30');
});

test('el detalle enlaza a la consulta que originó la solicitud', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-06 09:00:00',
    ]);
    $labRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $labRequest->id]);

    $this->actingAs($user)
        ->get(route('pacientes.laboratorios.show', [$patient, $labRequest]))
        ->assertOk()
        ->assertSee('Consulta del')
        ->assertSeeText('6 de agosto 2026')
        ->assertSee(route('consultas.show', $consultation->id).'#laboratorio');
});

test('la consulta enlaza al detalle de cada solicitud de laboratorio', function (): void {
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
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $labRequest->id]);

    $this->actingAs($user)
        ->get(route('consultas.show', $consultation))
        ->assertOk()
        ->assertSeeText('Ver detalle')
        ->assertSee(route('pacientes.laboratorios.show', [$patient, $labRequest]));
});

test('el listado de laboratorios diferencia pendiente, con resultado y completado', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $pendiente = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    $conResultado = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    $completado = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'received',
    ]);

    $itemConResultado = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $conResultado->id,
    ]);
    LaboratoryItemResult::factory()->create([
        'laboratory_request_item_id' => $itemConResultado->id,
        'consultation_id' => $consultation->id,
    ]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $pendiente->id]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $completado->id]);

    $this->actingAs($user)
        ->get(route('pacientes.laboratorios', $patient))
        ->assertOk()
        ->assertSeeText('Pendiente')
        ->assertSeeText('Con resultado')
        ->assertSeeText('Completado')
        ->assertDontSeeText('Recibido');
});

test('la ficha del paciente muestra el resultado dentro del historial de laboratorios', function (): void {
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
        'status' => 'received',
    ]);
    $item = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $labRequest->id,
        'exam_name' => 'Hemograma',
    ]);
    LaboratoryItemResult::factory()->create([
        'laboratory_request_item_id' => $item->id,
        'consultation_id' => $consultation->id,
        'value' => '12.5',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.show', $patient))
        ->assertOk()
        ->assertSeeText('Completado')
        ->assertSeeText('Resultado: 12.5');
});
