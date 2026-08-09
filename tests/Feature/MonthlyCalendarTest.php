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

test('el calendario marca los días con consulta registrada', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-15 09:00:00',
    ]);

    Livewire::actingAs($user)
        ->test('monthly-calendar', ['patient' => $patient])
        ->assertOk()
        ->assertSee('dusk="calendar-consultation-15"', false)
        ->assertSee(route('consultas.show', $consultation->id));
});

test('el calendario permite navegar entre meses sin duplicar registros', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-10 09:00:00',
    ]);
    Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-07-20 09:00:00',
    ]);

    $component = Livewire::actingAs($user)->test('monthly-calendar', ['patient' => $patient]);

    // Mes actual (agosto): solo la consulta del 10
    $component->assertSee('dusk="calendar-consultation-10"', false)
        ->assertDontSee('dusk="calendar-consultation-20"', false);

    // Mes siguiente (septiembre): sin consultas
    $component->call('nextMonth')
        ->assertDontSee('dusk="calendar-consultation-10"', false)
        ->assertDontSee('dusk="calendar-consultation-20"', false);

    // Vuelta a agosto y luego a julio: la consulta del 20 aparece solo en julio
    $component->call('prevMonth')
        ->assertSee('dusk="calendar-consultation-10"', false)
        ->assertDontSee('dusk="calendar-consultation-20"', false);

    $component->call('prevMonth')
        ->assertSee('dusk="calendar-consultation-20"', false)
        ->assertDontSee('dusk="calendar-consultation-10"', false);

    expect(Consultation::count())->toBe(2);
});

test('el calendario aparece en el perfil del paciente', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
        'consultation_date' => '2026-08-07 09:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.show', $patient))
        ->assertOk()
        ->assertSeeText('Calendario de consultas')
        ->assertSee('dusk="consultation-calendar"', false);
});
