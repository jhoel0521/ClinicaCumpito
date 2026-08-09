<?php

use App\Models\ClinicSetting;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\ClinicalDocumentService;
use Carbon\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-07 10:00:00');
    ClinicSetting::create(['name' => 'Clínica Cumpito Test']);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function renderDocument(string $view, mixed $doc): string
{
    return view($view, ['doc' => $doc])->render();
}

test('la receta impresa muestra la edad histórica en meses, no la edad actual', function (): void {
    $doctor = Doctor::factory()->create(['specialty' => 'Especialista en Pediatría']);
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    // Nació el 7 de enero de 2025: hoy (agosto 2026) tiene 19 meses,
    // pero la consulta fue a los 12 meses exactos
    $patient = Patient::factory()->create(['date_of_birth' => '2025-01-07']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-01-07 09:00:00',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

    $doc = app(ClinicalDocumentService::class)->receta($prescription);
    $html = renderDocument('documents.receta', $doc);

    expect($doc->ageText)->toBe('12 meses')
        ->and($html)->toContain('12 meses')
        ->and($html)->not->toContain('19 meses');
});

test('la receta impresa de un paciente mayor de 2 años muestra años y meses', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['date_of_birth' => '2024-04-17']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-02 09:00:00', // 2 años, 3 meses y 16 días
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

    $doc = app(ClinicalDocumentService::class)->receta($prescription);
    $html = renderDocument('documents.receta', $doc);

    expect($doc->ageText)->toBe('2 años, 3 meses y 16 días')
        ->and($html)->toContain('2 años, 3 meses y 16 días');
});

test('la receta impresa de un lactante menor de 2 meses muestra meses y días', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['date_of_birth' => '2026-01-01']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-07 09:00:00', // 7 meses y 6 días
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

    $doc = app(ClinicalDocumentService::class)->receta($prescription);

    expect($doc->ageText)->toBe('7 meses y 6 días');
});

test('la orden de laboratorio impresa usa la misma edad histórica del paciente', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['date_of_birth' => '2025-01-07']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-01-07 09:00:00', // 12 meses
    ]);
    $lab = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'received',
    ]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $lab->id]);

    $doc = app(ClinicalDocumentService::class)->ordenLaboratorio($lab);
    $html = renderDocument('documents.orden-laboratorio', $doc);

    expect($doc->ageText)->toBe('12 meses')
        ->and($html)->toContain('12 meses')
        ->and($html)->not->toContain('19 meses');
});

test('los endpoints de documentos responden con PDF', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['date_of_birth' => '2025-01-07']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-01-07 09:00:00',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

    $response = $this->actingAs($user)
        ->get(route('documentos.recetas.pdf', $prescription))
        ->assertOk();

    expect(str_contains((string) $response->headers->get('content-type'), 'application/pdf'))->toBeTrue();
});
