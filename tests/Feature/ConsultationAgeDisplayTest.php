<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-07 10:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

test('el banner de una consulta guardada muestra la edad que el paciente tenía en esa fecha', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    // Nació el 7 de enero de 2026 -> el 7 de agosto de 2026 tiene 7 meses exactos
    $patient = Patient::factory()->create([
        'date_of_birth' => '2026-01-07',
    ]);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:30:00',
    ]);

    $this->actingAs($user)
        ->get(route('consultas.show', $consultation))
        ->assertOk()
        ->assertSeeText('Edad en la consulta: 7 meses');
});

test('el banner de una consulta finalizada muestra la edad histórica aunque hoy sea mayor', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    // Nació el 10 de febrero de 2026 -> el 7 de agosto de 2026 tiene 5 meses y 28 días
    $patient = Patient::factory()->create([
        'date_of_birth' => '2026-02-10',
    ]);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-07 09:30:00',
    ]);

    $this->actingAs($user)
        ->get(route('consultas.show', $consultation))
        ->assertOk()
        ->assertSeeText('Edad en la consulta: 5 meses y 28 días')
        ->assertDontSeeText('Edad actual');
});

test('una consulta a los 12 meses sigue mostrando "12 meses" aunque hoy tenga más edad', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    // Nació el 7 de enero de 2025 -> el 7 de enero de 2026 tiene 12 meses exactos
    $patient = Patient::factory()->create([
        'date_of_birth' => '2025-01-07',
    ]);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-01-07 09:30:00',
    ]);

    // Hoy (agosto 2026) el paciente tiene 19 meses, pero la consulta debe mostrar 12
    $this->actingAs($user)
        ->get(route('consultas.show', $consultation))
        ->assertOk()
        ->assertSeeText('Edad en la consulta: 12 meses')
        ->assertDontSeeText('Edad actual');
});

test('el banner de un borrador muestra la edad actual del paciente', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    // Nació el 7 de enero de 2026 -> hoy (7 agosto 2026) tiene 7 meses exactos
    $patient = Patient::factory()->create([
        'date_of_birth' => '2026-01-07',
    ]);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
        'consultation_date' => '2026-07-01 09:30:00',
    ]);

    $this->actingAs($user)
        ->get(route('consultas.show', $consultation))
        ->assertOk()
        ->assertSeeText('Edad actual: 7 meses')
        ->assertDontSeeText('Edad en la consulta');
});

test('el banner no muestra edad si el paciente no tiene fecha de nacimiento', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create([
        'date_of_birth' => null,
    ]);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:30:00',
    ]);

    $this->actingAs($user)
        ->get(route('consultas.show', $consultation))
        ->assertOk()
        ->assertDontSeeText('Edad en la consulta')
        ->assertDontSeeText('Edad actual');
});
