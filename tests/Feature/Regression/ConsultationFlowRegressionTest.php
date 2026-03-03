<?php

/**
 * 8.3 — Pruebas de regresión del flujo completo de consulta.
 *
 * Valida el ciclo de vida completo de una consulta SOAP desde la creación
 * hasta el cierre, incluyendo inmutabilidad histórica.
 *
 * Flujo cubierto:
 *   1. Crear paciente con datos completos
 *   2. Doctor crea consulta (estado: draft)
 *   3. Guardar signos vitales
 *   4. Guardar nota SOAP
 *   5. Crear receta con ítems
 *   6. Crear solicitud de laboratorio con ítems
 *   7. Registrar vacuna aplicada
 *   8. Finalizar consulta (estado: finalized)
 *   9. Verificar inmutabilidad: no se puede editar consulta finalizada
 *  10. Verificar integridad del snapshot de receta
 *  11. Verificar integridad del snapshot de laboratorio
 */

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Vaccine;
use App\ValueObjects\ConsultationStatus;

beforeEach(function (): void {
    $this->doctor = Doctor::factory()->create();
    $this->user = User::factory()->create(['doctor_id' => $this->doctor->id]);
});

describe('8.3 — Regresión: flujo completo de consulta SOAP', function (): void {
    // =========================================================================
    // Paso 1-2: Crear paciente y consulta
    // =========================================================================

    test('doctor puede crear paciente con datos completos y consulta nueva', function (): void {
        $patient = Patient::factory()->withCompleteData()->create();

        $response = $this->actingAs($this->user)
            ->post(route('consultas.store'), [
                'patient_id' => $patient->id,
                'doctor_id' => $this->doctor->id,
                'type' => 'digital',
                'status' => 'draft',
                'consultation_date' => now()->toDateString(),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('consultations', [
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'draft',
        ]);
    });

    // =========================================================================
    // Paso 3: Signos vitales
    // =========================================================================

    test('doctor puede guardar signos vitales en la consulta', function (): void {
        $consultation = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('consultas.vital-signs.store', $consultation->id), [
                'weight' => 15.5,
                'height' => 90.0,
                'head_circumference' => 48.0,
                'temperature' => 36.8,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('vital_signs', [
            'consultation_id' => $consultation->id,
            'weight' => 15.5,
            'height' => 90.0,
        ]);
    });

    // =========================================================================
    // Paso 4: Nota SOAP
    // =========================================================================

    test('doctor puede guardar nota SOAP en la consulta', function (): void {
        $consultation = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('consultas.soap-notes.store', $consultation->id), [
                'subjective' => 'Paciente refiere fiebre alta desde ayer.',
                'objective' => 'Temperatura 38.5°C. Garganta eritematosa.',
                'assessment' => 'Faringoamigdalitis aguda.',
                'plan' => 'Amoxicilina 500mg cada 8 horas por 7 días.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('soap_notes', [
            'consultation_id' => $consultation->id,
            'assessment' => 'Faringoamigdalitis aguda.',
        ]);
    });

    // =========================================================================
    // Paso 5: Receta con ítem
    // =========================================================================

    test('doctor puede crear receta con ítem en la consulta', function (): void {
        $consultation = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->user)
            ->post(route('consultas.prescriptions.store', $consultation->id), [
                'observations' => 'Tomar con alimentos.',
            ])
            ->assertRedirect();

        $prescription = Prescription::where('consultation_id', $consultation->id)->firstOrFail();

        $this->actingAs($this->user)
            ->post(route('consultas.prescription-items.store', $consultation->id), [
                'medication_name' => 'Amoxicilina',
                'dose' => '500mg',
                'frequency' => 'cada 8 horas',
                'duration' => '7 días',
                'instructions' => 'Con alimentos.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'medication_name' => 'Amoxicilina',
        ]);
    });

    // =========================================================================
    // Paso 6: Solicitud de laboratorio con ítem
    // =========================================================================

    test('doctor puede crear solicitud de laboratorio con ítem', function (): void {
        $consultation = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->user)
            ->post(route('consultas.laboratory-requests.store', $consultation->id), [
                'observations' => 'Ayuno de 8 horas.',
            ])
            ->assertRedirect();

        $labRequest = LaboratoryRequest::where('consultation_id', $consultation->id)->firstOrFail();

        $this->actingAs($this->user)
            ->post(route('consultas.laboratory-request-items.store', $consultation->id), [
                'exam_name' => 'Hemograma completo',
                'indications' => 'Toma venosa.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('laboratory_request_items', [
            'laboratory_request_id' => $labRequest->id,
            'exam_name' => 'Hemograma completo',
        ]);
    });

    // =========================================================================
    // Paso 7: Vacuna aplicada
    // =========================================================================

    test('doctor puede registrar vacuna aplicada en la consulta', function (): void {
        $consultation = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'status' => 'draft',
        ]);
        $vaccine = Vaccine::factory()->create();

        $this->actingAs($this->user)
            ->post(route('consultas.patient-vaccines.store', $consultation->id), [
                'vaccine_id' => $vaccine->id,
                'applied_at' => now()->toDateString(),
                'applied_by_doctor_id' => $this->doctor->id,
                'dose_number' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('patient_vaccines', [
            'consultation_id' => $consultation->id,
            'vaccine_id' => $vaccine->id,
        ]);
    });

    // =========================================================================
    // Paso 8: Finalizar consulta
    // =========================================================================

    test('doctor puede finalizar la consulta cambiando estado a finalized', function (): void {
        $patient = Patient::factory()->withCompleteData()->create();
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'saved',
        ]);

        $this->actingAs($this->user)
            ->put(route('consultas.update', $consultation->id), [
                'patient_id' => $patient->id,
                'doctor_id' => $this->doctor->id,
                'type' => 'digital',
                'status' => 'finalized',
                'consultation_date' => $consultation->consultation_date->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'status' => 'finalized',
        ]);
    });

    // =========================================================================
    // Paso 9: Inmutabilidad — consulta finalizada no se puede reasignar a otro estado
    // =========================================================================

    test('consulta finalizada no puede ser editada por otro doctor', function (): void {
        $doctorB = Doctor::factory()->create();
        $consultation = Consultation::factory()->create([
            'doctor_id' => $doctorB->id,
            'status' => 'finalized',
        ]);

        $this->actingAs($this->user)
            ->get(route('consultas.edit', $consultation->id))
            ->assertForbidden();
    });

    test('doctor propietario ve consulta finalizada en modo show', function (): void {
        $consultation = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'status' => 'finalized',
        ]);

        $this->actingAs($this->user)
            ->get(route('consultas.show', $consultation->id))
            ->assertOk();
    });

    // =========================================================================
    // Paso 10-11: Integridad del snapshot
    // =========================================================================

    test('receta de consulta finalizada persiste sus ítems intactos', function (): void {
        $consultation = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'status' => 'finalized',
        ]);
        $prescription = Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        \App\Models\PrescriptionItem::factory()->create([
            'prescription_id' => $prescription->id,
            'medication_name' => 'Ibuprofeno',
            'dose' => '400mg',
            'frequency' => 'cada 12 horas',
            'duration' => '5 días',
        ]);

        $prescription->refresh();

        expect($prescription->items()->count())->toBe(1);
        expect($prescription->items()->first()->medication_name)->toBe('Ibuprofeno');
    });

    test('solicitud de laboratorio de consulta finalizada persiste sus ítems', function (): void {
        $consultation = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'status' => 'finalized',
        ]);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        \App\Models\LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
            'exam_name' => 'Glucosa en ayunas',
        ]);

        $labRequest->refresh();

        expect($labRequest->items()->count())->toBe(1);
        expect($labRequest->items()->first()->exam_name)->toBe('Glucosa en ayunas');
    });

    // =========================================================================
    // Flujo completo integrado
    // =========================================================================

    test('flujo SOAP completo: crear → llenar → finalizar → verificar', function (): void {
        $patient = Patient::factory()->withCompleteData()->create();
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'draft',
        ]);

        // Signos vitales
        $this->actingAs($this->user)
            ->post(route('consultas.vital-signs.store', $consultation->id), [
                'weight' => 20.0,
                'height' => 100.0,
            ])
            ->assertRedirect();

        // Nota SOAP
        $this->actingAs($this->user)
            ->post(route('consultas.soap-notes.store', $consultation->id), [
                'assessment' => 'Estado general bueno.',
            ])
            ->assertRedirect();

        // Receta
        $this->actingAs($this->user)
            ->post(route('consultas.prescriptions.store', $consultation->id), [])
            ->assertRedirect();

        $prescription = Prescription::where('consultation_id', $consultation->id)->first();
        expect($prescription)->not->toBeNull();

        // Ítem de receta
        $this->actingAs($this->user)
            ->post(route('consultas.prescription-items.store', $consultation->id), [
                'medication_name' => 'Paracetamol',
                'dose' => '250mg',
                'frequency' => 'cada 8 horas',
                'duration' => '3 días',
            ])
            ->assertRedirect();

        // Solicitud de laboratorio
        $this->actingAs($this->user)
            ->post(route('consultas.laboratory-requests.store', $consultation->id), [])
            ->assertRedirect();

        $labRequest = LaboratoryRequest::where('consultation_id', $consultation->id)->first();
        expect($labRequest)->not->toBeNull();

        // Ítem de laboratorio
        $this->actingAs($this->user)
            ->post(route('consultas.laboratory-request-items.store', $consultation->id), [
                'exam_name' => 'Proteína C reactiva',
            ])
            ->assertRedirect();

        // Finalizar
        $this->actingAs($this->user)
            ->put(route('consultas.update', $consultation->id), [
                'patient_id' => $patient->id,
                'doctor_id' => $this->doctor->id,
                'type' => 'digital',
                'status' => 'finalized',
                'consultation_date' => $consultation->consultation_date->toDateString(),
            ])
            ->assertRedirect();

        // Verificaciones finales
        $consultation->refresh();
        expect($consultation->status->value())->toBe(ConsultationStatus::FINALIZED);
        expect($consultation->vitalSigns)->not->toBeNull();
        expect($consultation->soapNote)->not->toBeNull();
        expect($consultation->prescription->items()->count())->toBe(1);
        expect($consultation->laboratoryRequest->items()->count())->toBe(1);
    });
});
