<?php

use App\DTOs\SoapNoteDTO;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\SoapNote;
use App\Models\User;
use App\Models\VitalSign;
use App\Services\SoapNoteService;
use Carbon\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-07 10:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

test('editar una consulta guardada no genera una consulta duplicada', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:00:00',
    ]);

    $service = new SoapNoteService;
    $service->upsert($consultation->id, SoapNoteDTO::fromArray([
        'subjective' => 'Motivo inicial',
        'objective' => 'Evaluación inicial',
        'assessment' => 'Diagnóstico inicial',
        'plan' => 'Plan inicial',
    ]));

    // El médico corrige la nota (segunda edición sobre la misma consulta)
    $service->upsert($consultation->id, SoapNoteDTO::fromArray([
        'subjective' => 'Motivo corregido',
        'objective' => 'Evaluación corregida',
        'assessment' => 'Diagnóstico corregido',
        'plan' => 'Plan corregido',
    ]));

    expect(Consultation::count())->toBe(1)
        ->and(SoapNote::count())->toBe(1)
        ->and($consultation->soapNote()->first()->subjective)->toBe('Motivo corregido');
});

test('el resumen del feed refleja el formulario tras guardar y recargar', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    // Nació el 7 de enero de 2026 -> 7 meses en la consulta
    $patient = Patient::factory()->create(['date_of_birth' => '2026-01-07']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'type' => 'digital',
        'consultation_date' => '2026-08-07 09:00:00',
    ]);

    SoapNote::factory()->create([
        'consultation_id' => $consultation->id,
        'subjective' => 'Motivo: fiebre de 3 días',
        'objective' => 'Paciente alerta y reactivo',
        'assessment' => 'Diagnóstico: resfriado común',
        'plan' => 'Indicaciones: hidratación y control',
    ]);
    VitalSign::factory()->create([
        'consultation_id' => $consultation->id,
        'weight' => 8.5,
        'height' => 68.5,
    ]);
    $prescription = Prescription::factory()->create([
        'consultation_id' => $consultation->id,
        'reason' => 'Resfriado común',
    ]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);
    $labRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $labRequest->id]);

    $this->actingAs($user)
        ->get(route('pacientes.feed', $patient))
        ->assertOk()
        // Fecha y edad del paciente
        ->assertSeeText('7 de agosto 2026')
        ->assertSeeText('Edad: 7 meses')
        ->assertSeeText('Control de los 7 meses')
        // Estado y tipo
        ->assertSeeText('Finalizada')
        ->assertSeeText('Digital')
        // Motivo, evaluación, diagnóstico e indicaciones (SOAP completo)
        ->assertSeeText('Motivo: fiebre de 3 días')
        ->assertSeeText('Paciente alerta y reactivo')
        ->assertSeeText('Diagnóstico: resfriado común')
        ->assertSeeText('Indicaciones: hidratación y control')
        // Signos vitales
        ->assertSeeText('8.5 kg')
        ->assertSeeText('68.5 cm')
        // Receta y laboratorio solicitado
        ->assertSeeText('Receta')
        ->assertSeeText('Laboratorio pendiente');
});

test('la consulta guardada aparece en el historial del paciente sin duplicarse', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:00:00',
    ]);

    $response = $this->actingAs($user)->get(route('pacientes.feed', $patient))->assertOk();

    $content = $response->getContent();

    expect(substr_count($content, 'dusk="consultation-summary-'))->toBe(1);
});

test('la consulta permanece tras finalizar y reabrirla desde la ficha', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-07 09:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('consultas.show', $consultation))
        ->assertOk()
        ->assertSeeText('Edad en la consulta');

    expect(Consultation::find($consultation->id)->id)->toBe($consultation->id);
});
