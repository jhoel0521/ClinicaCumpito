<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-07 10:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

test('el seguimiento identifica los controles con consulta registrada', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    // Nació el 7 de enero de 2026
    $patient = Patient::factory()->create(['date_of_birth' => '2026-01-07']);
    Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-02-07 09:00:00', // 1 mes
    ]);
    Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:00:00', // 7 meses
    ]);

    Livewire::actingAs($user)
        ->test('monthly-follow-up', ['patient' => $patient])
        ->assertOk()
        ->assertSee('dusk="control-month-1"', false)
        ->assertSee('dusk="control-month-7"', false)
        ->assertSee('dusk="control-missing-2"', false)
        ->assertSee('2')
        ->assertSee('de 25 controles');
});

test('el seguimiento asocia cada mes a la consulta más reciente de ese mes', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['date_of_birth' => '2026-01-07']);

    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-07 09:00:00', // 7 meses
    ]);

    Livewire::actingAs($user)
        ->test('monthly-follow-up', ['patient' => $patient])
        ->assertOk()
        ->assertSee(route('consultas.show', $consultation->id));
});

test('el seguimiento muestra los 25 meses aunque no todos tengan consulta', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['date_of_birth' => '2026-01-07']);

    Livewire::actingAs($user)
        ->test('monthly-follow-up', ['patient' => $patient])
        ->assertOk()
        ->assertSee('dusk="control-missing-0"', false)
        ->assertSee('dusk="control-missing-12"', false)
        ->assertSee('dusk="control-missing-24"', false)
        ->assertSee('Recién nacido')
        ->assertSee('Mes 24')
        ->assertSee('0')
        ->assertSee('de 25 controles');
});

test('el seguimiento no crea consultas ni se muestra sin fecha de nacimiento', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['date_of_birth' => null]);

    Livewire::actingAs($user)
        ->test('monthly-follow-up', ['patient' => $patient])
        ->assertOk()
        ->assertDontSee('controles mensuales')
        ->assertDontSee('Mes 1');

    expect(Consultation::count())->toBe(0);
});

test('el seguimiento aparece en el perfil del paciente', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create(['date_of_birth' => '2026-01-07']);
    Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.show', $patient))
        ->assertOk()
        ->assertSeeText('Controles mensuales 0–24 meses')
        ->assertSee('dusk="monthly-controls"', false);
});
