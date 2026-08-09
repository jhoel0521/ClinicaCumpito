<?php

use App\Models\ClinicSetting;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\ClinicalDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;

beforeEach(function (): void {
    ClinicSetting::create(['name' => 'Clínica Cumpito Test']);
});

test('el PDF de la receta conserva el formato vertical del diseño aprobado y tiene una sola página', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->count(2)->create(['prescription_id' => $prescription->id]);

    $doc = app(ClinicalDocumentService::class)->receta($prescription);
    $pdf = Pdf::loadView('documents.receta', ['doc' => $doc])->setPaper($doc->paper->toDompdf(), 'mm');
    $binary = $pdf->output();

    // 129,91 × 210,08 mm = 368,25 × 595,5 pt, igual al PDF de Canva.
    preg_match('/\/MediaBox\s*\[\s*[\d.]+\s+[\d.]+\s+([\d.]+)\s+([\d.]+)\s*\]/', $binary, $m);

    expect((float) $m[1])->toBeGreaterThan(368.0)
        ->and((float) $m[1])->toBeLessThan(369.0)
        ->and((float) $m[2])->toBeGreaterThan(595.0)
        ->and((float) $m[2])->toBeLessThan(596.0)
        ->and(preg_match_all('/\/Type\s*\/Page[^s]/', $binary))->toBe(1);
});

test('la receta incrusta el arte original y muestra los datos clínicos sobre el formulario', function (): void {
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create(['full_name' => 'Mateo Andrés Pérez']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-09 09:00:00',
    ]);
    $prescription = Prescription::factory()->create([
        'consultation_id' => $consultation->id,
        'reason' => 'Faringitis aguda',
        'observations' => 'Mantener buena hidratación.',
    ]);
    PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'medication_name' => 'Paracetamol',
    ]);

    $doc = app(ClinicalDocumentService::class)->receta($prescription);
    $html = view('documents.receta', ['doc' => $doc])->render();

    expect($html)->toContain('data:image/jpeg;base64,')
        ->and($html)->toContain('Mateo Andrés Pérez')
        ->and($html)->toContain('09/08/2026')
        ->and($html)->toContain('Faringitis aguda')
        ->and($html)->toContain('Paracetamol')
        ->and($html)->toContain('Mantener buena hidratación.');
});

test('el PDF de la orden de laboratorio mide hoja oficio completa', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $lab = LaboratoryRequest::factory()->create(['consultation_id' => $consultation->id, 'status' => 'pending']);
    LaboratoryRequestItem::factory()->count(3)->create(['laboratory_request_id' => $lab->id]);

    $doc = app(ClinicalDocumentService::class)->ordenLaboratorio($lab);
    $pdf = Pdf::loadView('documents.orden-laboratorio', ['doc' => $doc])->setPaper($doc->paper->toDompdf(), 'mm');
    $binary = $pdf->output();

    // 215,9 × 330,2 mm = 612 × 936 pt
    preg_match('/\/MediaBox\s*\[\s*[\d.]+\s+[\d.]+\s+([\d.]+)\s+([\d.]+)\s*\]/', $binary, $m);

    expect((float) $m[1])->toBeGreaterThan(610.0)
        ->and((float) $m[1])->toBeLessThan(614.0)
        ->and((float) $m[2])->toBeGreaterThan(934.0)
        ->and((float) $m[2])->toBeLessThan(938.0);
});

test('la receta con demasiados medicamentos marca desbordamiento y bloquea la descarga', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->count(12)->create([
        'prescription_id' => $prescription->id,
        'instructions' => 'Instrucciones extensas con indicaciones detalladas para la administración del medicamento en casa.',
    ]);

    $doc = app(ClinicalDocumentService::class)->receta($prescription);

    expect($doc->overflow)->toBeTrue();

    $this->actingAs($user)
        ->get(route('documentos.recetas.preview', $prescription))
        ->assertOk()
        ->assertSee('Contenido excede el espacio')
        ->assertSee('El contenido no cabe en una sola página');
});

test('la receta con pocos medicamentos no marca desbordamiento', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->count(2)->create(['prescription_id' => $prescription->id]);

    $doc = app(ClinicalDocumentService::class)->receta($prescription);

    expect($doc->overflow)->toBeFalse();
});

test('la orden de laboratorio agrupa los estudios por categoría', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $catHematologia = LaboratoryCategory::factory()->create(['name' => 'Hematología']);
    $catOrina = LaboratoryCategory::factory()->create(['name' => 'Orina']);
    $examenHemograma = LaboratoryExam::factory()->create(['category_id' => $catHematologia->id, 'name' => 'Hemograma']);
    $examenOrina = LaboratoryExam::factory()->create(['category_id' => $catOrina->id, 'name' => 'Examen general de orina']);

    $lab = LaboratoryRequest::factory()->create(['consultation_id' => $consultation->id, 'status' => 'pending']);
    LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $lab->id,
        'exam_name' => $examenOrina->name,
    ]);
    LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $lab->id,
        'exam_name' => $examenHemograma->name,
    ]);
    LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $lab->id,
        'exam_name' => 'Examen personalizado del doctor',
    ]);

    $doc = app(ClinicalDocumentService::class)->ordenLaboratorio($lab);
    $html = view('documents.orden-laboratorio', ['doc' => $doc])->render();

    expect($doc->isValid())->toBeTrue()
        ->and($html)->toContain('Hematología')
        ->and($html)->toContain('Orina')
        ->and($html)->toContain('Otros')
        ->and($html)->toContain('Hemograma')
        ->and($html)->toContain('Examen general de orina')
        ->and($html)->toContain('Examen personalizado del doctor');
});

test('la vista previa muestra el documento con el nombre del paciente', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['full_name' => 'Aitana Aguilar Pérez']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

    $this->actingAs($user)
        ->get(route('documentos.recetas.preview', $prescription))
        ->assertOk()
        ->assertSee('Receta médica')
        ->assertSee('Aitana Aguilar Pérez')
        ->assertSee('Descargar PDF')
        ->assertSee('Imprimir')
        ->assertSee('Editar');
});

test('no se genera receta sin medicamentos y redirige con error', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);

    $this->actingAs($user)
        ->get(route('documentos.recetas.preview', $prescription))
        ->assertRedirect(route('consultas.show', $consultation->id))
        ->assertSessionHasErrors('documento');
});

test('el nombre del archivo identifica al paciente y la fecha', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['full_name' => 'Aitana Aguilar']);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-07 09:00:00',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

    $doc = app(ClinicalDocumentService::class)->receta($prescription);

    expect($doc->fileName())->toBe('receta_aitana-aguilar_2026-08-07.pdf');
});
