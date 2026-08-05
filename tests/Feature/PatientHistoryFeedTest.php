<?php

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
use Illuminate\Support\Str;

test('el feed resume la información clínica registrada en cada consulta', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);

    $subjective = 'Motivo de consulta detallado para confirmar que el resumen del feed mantiene el contenido clínico sin desbordar la tarjeta visual.';
    SoapNote::factory()->create([
        'consultation_id' => $consultation->id,
        'subjective' => $subjective,
        'objective' => 'Paciente alerta, hidratado y con evaluación física registrada durante la consulta.',
        'assessment' => 'Cuadro respiratorio alto sin signos de alarma.',
        'plan' => 'Hidratación, medidas generales y control ante signos de alarma.',
    ]);
    VitalSign::factory()->create([
        'consultation_id' => $consultation->id,
        'weight' => 12.5,
        'height' => 91.2,
        'temperature' => 37.1,
        'head_circumference' => 48.3,
    ]);

    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    $medicationName = 'Medicamento que no debe aparecer en el feed';
    PrescriptionItem::factory()->count(2)->create([
        'prescription_id' => $prescription->id,
        'medication_name' => $medicationName,
    ]);

    $laboratoryRequest = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'received',
    ]);
    $laboratoryItem = LaboratoryRequestItem::factory()->create([
        'laboratory_request_id' => $laboratoryRequest->id,
        'exam_name' => 'Examen que no debe aparecer en el feed',
    ]);
    $this->actingAs($user)
        ->get(route('pacientes.feed', $patient))
        ->assertOk()
        ->assertSeeText('SOAP registrado')
        ->assertSeeText('Receta')
        ->assertSeeText('Laboratorio recibido')
        ->assertSee(route('consultas.pdf.recetas.single', [$consultation, $prescription]))
        ->assertSee(route('consultas.pdf.laboratorio.single', [$consultation, $laboratoryRequest]))
        ->assertSeeText('Subjetivo')
        ->assertSeeText(Str::limit($subjective, 110))
        ->assertDontSeeText($subjective)
        ->assertSeeText('Peso')
        ->assertSeeText('12.5 kg')
        ->assertSeeText('91.2 cm')
        ->assertSeeText('37.1°C')
        ->assertSeeText('48.3 cm')
        ->assertDontSeeText($laboratoryItem->exam_name)
        ->assertDontSeeText($medicationName);
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
        ->assertSeeText('Laboratorio pendiente');
});

test('el resumen del feed no lista los exámenes ni parámetros solicitados', function (): void {
    $user = User::factory()->create();
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'saved',
    ]);

    // Un coprológico con 7 parámetros + un hemograma con 10: como en la obs de la clienta
    $coprologico = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'pending',
    ]);
    LaboratoryRequestItem::factory()->count(7)->create([
        'laboratory_request_id' => $coprologico->id,
        'exam_name' => 'Coprológico (COPR)',
    ]);

    $hemograma = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultation->id,
        'status' => 'received',
    ]);
    LaboratoryRequestItem::factory()->count(10)->create([
        'laboratory_request_id' => $hemograma->id,
        'exam_name' => 'Hemograma',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.feed', $patient))
        ->assertOk()
        ->assertSeeText('Laboratorio pendiente')
        ->assertSeeText('Laboratorio recibido')
        ->assertDontSeeText('Coprológico (COPR)')
        ->assertDontSeeText('Hemograma');
});
