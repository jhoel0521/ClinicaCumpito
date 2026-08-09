<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\PatientVaccine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Models\Vaccine;

test('la ficha integral muestra todas las secciones del paciente abierto', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['date_of_birth' => '2024-01-07']);

    $consulta = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:00:00',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consulta->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);
    $lab = LaboratoryRequest::factory()->create([
        'consultation_id' => $consulta->id,
        'status' => 'pending',
    ]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $lab->id]);
    $vaccine = Vaccine::factory()->create(['name' => 'Influenza']);
    PatientVaccine::factory()->create([
        'patient_id' => $patient->id,
        'consultation_id' => $consulta->id,
        'vaccine_id' => $vaccine->id,
        'applied_at' => '2026-08-07 09:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.show', $patient))
        ->assertOk()
        // Datos personales y edad actual
        ->assertSeeText($patient->full_name)
        ->assertSeeText('Edad')
        // Última consulta
        ->assertSeeText('Última Consulta')
        ->assertSeeText('07/08/2026')
        // Historial de consultas, recetas, laboratorios y vacunas
        ->assertSeeText('Historial de Consultas')
        ->assertSeeText('Historial de Recetas')
        ->assertSeeText('Historial de Laboratorios')
        ->assertSeeText('Historial de Vacunas')
        // Controles mensuales y calendario
        ->assertSeeText('Controles mensuales')
        ->assertSeeText('Calendario de consultas')
        // Esquema de vacunación
        ->assertSeeText('Esquema de vacunación')
        // Accesos directos
        ->assertSee(route('pacientes.recetas', $patient))
        ->assertSee(route('pacientes.laboratorios', $patient))
        ->assertSee(route('pacientes.vacunas', $patient))
        ->assertSee(route('pacientes.feed', $patient));
});

test('los contadores de la ficha coinciden con los registros existentes', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();

    Consultation::factory()->count(3)->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $response = $this->actingAs($user)->get(route('pacientes.show', $patient));

    $response->assertOk();

    expect(Consultation::where('patient_id', $patient->id)->count())->toBe(3);
});

test('cada sección de la ficha pertenece al paciente abierto y no a otros', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patientA = Patient::factory()->create(['full_name' => 'Paciente Alfa']);
    $patientB = Patient::factory()->create(['full_name' => 'Paciente Beta']);

    Consultation::factory()->create([
        'patient_id' => $patientA->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    Consultation::factory()->create([
        'patient_id' => $patientB->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $response = $this->actingAs($user)->get(route('pacientes.show', $patientA));

    $response->assertOk()
        ->assertSeeText('Paciente Alfa');

    expect($response->getContent())->not->toContain('Paciente Beta');
});
