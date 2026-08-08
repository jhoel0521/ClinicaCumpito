<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientVaccine;
use App\Models\User;
use App\Models\Vaccine;

test('el historial de vacunas muestra la vacuna y su fecha de aplicación', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $vaccine = Vaccine::factory()->create(['name' => 'BCG']);
    PatientVaccine::factory()->create([
        'patient_id' => $patient->id,
        'consultation_id' => $consultation->id,
        'vaccine_id' => $vaccine->id,
        'applied_at' => '2026-08-06 09:00:00',
        'dose_number' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.vacunas', $patient))
        ->assertOk()
        ->assertSeeText('06/08/2026')
        ->assertSeeText('BCG')
        ->assertSeeText('1ª');
});

test('el historial de vacunas ya no distingue "acá u otro lugar"', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    PatientVaccine::factory()->create([
        'patient_id' => $patient->id,
        'consultation_id' => $consultation->id,
        'applied_at' => '2026-08-06 09:00:00',
        'applied_elsewhere' => true,
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.vacunas', $patient))
        ->assertOk()
        ->assertDontSeeText('Otro lugar')
        ->assertDontSeeText('Administrado por')
        ->assertDontSeeText('Próxima dosis')
        ->assertDontSeeText('Lote');
});

test('el historial de vacunas enlaza a la consulta donde se registró', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $pv = PatientVaccine::factory()->create([
        'patient_id' => $patient->id,
        'consultation_id' => $consultation->id,
        'applied_at' => '2026-08-06 09:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.vacunas', $patient))
        ->assertOk()
        ->assertSee(route('consultas.show', $consultation->id).'#vacunas')
        ->assertSee('dusk="vaccine-consultation-'.$pv->id.'"', false);
});

test('el historial de vacunas muestra el doctor que la registró', function (): void {
    $doctor = Doctor::factory()->create(['full_name' => 'Dra. Ana Rojas']);
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    PatientVaccine::factory()->create([
        'patient_id' => $patient->id,
        'consultation_id' => $consultation->id,
        'applied_by_doctor_id' => $doctor->id,
        'applied_at' => '2026-08-06 09:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.vacunas', $patient))
        ->assertOk()
        ->assertSeeText('Dra. Ana Rojas');
});

test('la sección de vacunas del perfil muestra las últimas con enlace a la consulta', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    PatientVaccine::factory()->create([
        'patient_id' => $patient->id,
        'consultation_id' => $consultation->id,
        'applied_at' => '2026-08-06 09:00:00',
        'dose_number' => 2,
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.show', $patient))
        ->assertOk()
        ->assertSeeText('Historial de Vacunas')
        ->assertSeeText('06/08/2026')
        ->assertSeeText('2ª')
        ->assertSee(route('consultas.show', $consultation->id).'#vacunas');
});
