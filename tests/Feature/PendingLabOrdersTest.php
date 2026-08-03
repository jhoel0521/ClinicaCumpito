<?php

use App\Models\Consultation;
use App\Models\LaboratoryItemResult;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('una consulta nueva muestra los laboratorios pendientes de consultas previas del paciente', function () {
    $patient = Patient::factory()->create();

    // Día 1: consulta con un laboratorio que quedó pendiente
    $consultaDia1 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now()->subDay(),
    ]);
    $requestPendiente = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultaDia1->id,
        'status' => 'pending',
    ]);
    LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $requestPendiente->id,
        'exam_name' => 'Coprológico (COPR)',
    ]);

    // Día 2: el paciente vuelve con los resultados
    $consultaDia2 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now(),
    ]);

    Livewire::test('consultation-laboratory', ['consultationId' => $consultaDia2->id])
        ->assertSet('labRequests', [])
        ->assertSet('pendingPreviousLabRequests', function (array $pending) use ($requestPendiente) {
            return count($pending) === 1
                && $pending[0]['id'] === $requestPendiente->id
                && $pending[0]['examName'] === 'Coprológico (COPR)'
                && isset($pending[0]['consultation_date']);
        });
});

test('los resultados se pueden cargar al día siguiente sobre la orden pendiente previa', function () {
    $patient = Patient::factory()->create();

    $consultaDia1 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now()->subDay(),
    ]);
    $requestPendiente = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultaDia1->id,
        'status' => 'pending',
    ]);
    $item = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $requestPendiente->id,
        'exam_name' => 'Hemograma',
        'parameter_name' => 'Hemoglobina',
    ]);

    $consultaDia2 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now(),
    ]);

    Livewire::test('consultation-laboratory', ['consultationId' => $consultaDia2->id])
        ->set("newResults.{$item->id}.value", '12.5')
        ->call('saveAllResults', $requestPendiente->id);

    $this->assertDatabaseHas('laboratory_item_results', [
        'laboratory_request_item_id' => $item->id,
        'consultation_id' => $consultaDia2->id,
        'value' => '12.5',
    ]);

    expect(LaboratoryItemResult::count())->toBe(1);
});
