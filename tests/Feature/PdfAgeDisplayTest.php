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
use Carbon\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-07 10:00:00');
    ClinicSetting::create(['name' => 'Clínica Cumpito Test']);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function renderPdf(string $view, array $data): string
{
    return view($view, $data)->render();
}

test('la receta impresa muestra la edad histórica en meses, no la edad actual', function (): void {
    $doctor = Doctor::factory()->create();
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

    $html = renderPdf('pdf.prescription-single', [
        'consultation' => $consultation,
        'prescription' => $prescription,
        'clinic' => ClinicSetting::current(),
    ]);

    expect($html)->toContain('Edad:')
        ->and($html)->toContain('12 meses')
        ->and($html)->not->toContain('19 meses')
        ->and($html)->not->toContain('1 año(s)');
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

    $html = renderPdf('pdf.prescription-single', [
        'consultation' => $consultation,
        'prescription' => $prescription,
        'clinic' => ClinicSetting::current(),
    ]);

    expect($html)->toContain('2 años, 3 meses y 16 días');
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

    $html = renderPdf('pdf.prescription-single', [
        'consultation' => $consultation,
        'prescription' => $prescription,
        'clinic' => ClinicSetting::current(),
    ]);

    expect($html)->toContain('7 meses y 6 días');
});

test('el laboratorio impreso usa la misma edad histórica del paciente', function (): void {
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

    $html = renderPdf('pdf.laboratory-single', [
        'consultation' => $consultation,
        'laboratoryRequest' => $lab,
        'clinic' => ClinicSetting::current(),
    ]);

    expect($html)->toContain('12 meses')
        ->and($html)->not->toContain('19 meses');
});

test('los endpoints de PDF responden correctamente', function (): void {
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
        ->get(route('consultas.pdf.recetas.single', [$consultation, $prescription]))
        ->assertOk();

    expect(str_contains((string) $response->headers->get('content-type'), 'application/pdf'))->toBeTrue();

    $this->actingAs($user)
        ->get(route('consultas.pdf.recetas.all', $consultation))
        ->assertOk();
});
