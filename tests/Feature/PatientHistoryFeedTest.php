<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryItemResult;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\SoapNote;
use App\Models\User;

test('el feed resume la información clínica registrada en cada consulta', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);

    SoapNote::factory()->create(['consultation_id' => $consultation->id]);

    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->count(2)->create(['prescription_id' => $prescription->id]);

    $laboratoryRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'received',
    ]);
    $laboratoryItem = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $laboratoryRequest->id,
    ]);
    LaboratoryItemResult::query()->create([
        'laboratory_request_item_id' => $laboratoryItem->id,
        'consultation_id' => $consultation->id,
        'value' => '12.5',
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.feed', $patient))
        ->assertOk()
        ->assertSeeText('SOAP registrado')
        ->assertSeeTextInOrder(['Receta', '2', 'medicamentos'])
        ->assertSeeTextInOrder(['Laboratorio solicitado', '1'])
        ->assertSeeText('Resultados registrados');
});

test('el feed identifica información clínica pendiente o ausente', function (): void {
    $user = User::factory()->create();
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'saved',
    ]);
    LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.feed', $patient))
        ->assertOk()
        ->assertSeeText('SOAP pendiente')
        ->assertSeeText('Sin receta')
        ->assertSeeTextInOrder(['Laboratorio solicitado', '1'])
        ->assertSeeText('Resultados pendientes');
});
