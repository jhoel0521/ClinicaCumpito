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

test('una consulta nueva no vuelve a mostrar laboratorios ya recibidos de consultas previas', function () {
    $patient = Patient::factory()->create();

    // Consulta 1: laboratorio que quedó pendiente
    $consulta1 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now()->subDays(2),
    ]);
    $pendiente = LaboratoryRequest::factory()->create([
        'consultation_id' => $consulta1->id,
        'status' => 'pending',
    ]);
    LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $pendiente->id,
        'exam_name' => 'Coprológico',
    ]);

    // Consulta 2: se registró el resultado y se marcó como recibido
    $consulta2 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now()->subDay(),
    ]);
    $recibido = LaboratoryRequest::factory()->create([
        'consultation_id' => $consulta2->id,
        'status' => 'received',
    ]);
    LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $recibido->id,
        'exam_name' => 'Hemograma',
    ]);

    // Consulta 3: solo debe aparecer el que sigue pendiente, nunca el recibido
    $consulta3 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now(),
    ]);

    Livewire::test('consultation-laboratory', ['consultationId' => $consulta3->id])
        ->assertSet('pendingPreviousLabRequests', function (array $pending) use ($pendiente, $recibido) {
            $ids = array_column($pending, 'id');

            return in_array($pendiente->id, $ids)
                && ! in_array($recibido->id, $ids);
        });
});

test('una consulta nueva no acumula los laboratorios históricos ya completados', function () {
    $patient = Patient::factory()->create();

    // Paciente recurrente: 6 consultas previas con laboratorios ya recibidos
    foreach (range(1, 6) as $i) {
        $consulta = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'consultation_date' => now()->subDays(30 - $i),
        ]);
        $lab = LaboratoryRequest::factory()->create([
            'consultation_id' => $consulta->id,
            'status' => 'received',
        ]);
        LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $lab->id,
            'exam_name' => 'Examen '.$i,
        ]);
    }

    $consultaNueva = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now(),
    ]);

    Livewire::test('consultation-laboratory', ['consultationId' => $consultaNueva->id])
        ->assertSet('pendingPreviousLabRequests', []);
});

test('una orden con resultados registrados no vuelve a aparecer en la siguiente consulta', function () {
    $patient = Patient::factory()->create();

    // Consulta 1: se solicita el laboratorio
    $consulta1 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now()->subDays(2),
    ]);
    $requestPendiente = LaboratoryRequest::factory()->create([
        'consultation_id' => $consulta1->id,
        'status' => 'pending',
    ]);
    $item = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $requestPendiente->id,
        'exam_name' => 'Hemograma',
    ]);

    // Consulta 2: se registra la respuesta (resultado) pero la orden queda pendiente
    $consulta2 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now()->subDay(),
    ]);
    Livewire::test('consultation-laboratory', ['consultationId' => $consulta2->id])
        ->set("newResults.{$item->id}.value", '12.5')
        ->call('saveAllResults', $requestPendiente->id);

    expect(LaboratoryItemResult::count())->toBe(1);

    // Consulta 3: el laboratorio ya atendido NO debe aparecer de nuevo
    $consulta3 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'consultation_date' => now(),
    ]);

    Livewire::test('consultation-laboratory', ['consultationId' => $consulta3->id])
        ->assertSet('pendingPreviousLabRequests', []);
});
