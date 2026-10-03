<?php

use App\Contracts\ScheduledVisitServiceContract;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\ScheduledVisit;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

/*
 * Agenda del dashboard: la doctora ve las visitas de los próximos 7/15/30
 * días y a quién recordarle porque debía venir y no vino.
 */
beforeEach(function (): void {
    Carbon::setTestNow('2026-10-03 10:00:00');

    $this->doctor = Doctor::factory()->create();
    $this->user = User::factory()->create(['doctor_id' => $this->doctor->id]);
    $this->actingAs($this->user);

    $this->tutor = User::factory()->create(['phone_number' => '76543210']);
    $this->patient = Patient::factory()->create(['full_name' => 'Thiago Méndez', 'user_id' => $this->tutor->id]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function agendaVisit(array $attributes): ScheduledVisit
{
    return ScheduledVisit::factory()->create($attributes);
}

test('la agenda del servicio filtra por doctor y rango de fechas', function (): void {
    $other = Doctor::factory()->create();
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-05']);
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-20']);
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $other->id, 'scheduled_for' => '2026-10-05']);

    $service = app(ScheduledVisitServiceContract::class);

    expect($service->agenda($this->doctor->id, '2026-10-03', '2026-10-09'))->toHaveCount(1)
        ->and($service->agenda($this->doctor->id, '2026-10-03', '2026-11-01'))->toHaveCount(2)
        ->and($service->agenda(null, '2026-10-03', '2026-10-09'))->toHaveCount(2);
});

test('atrasadas son las que pasaron sin consulta en los últimos 30 días', function (): void {
    $late = agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-01', 'reason' => 'Traer laboratorio']);
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-08-01']); // hace más de 30 días
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-03']); // es hoy, todavía no está atrasada

    $other = Patient::factory()->create();
    agendaVisit(['patient_id' => $other->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-09-25']);
    Consultation::factory()->create([
        'patient_id' => $other->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-09-26 09:00:00',
    ]); // vino: no hay que llamarlo

    $overdue = app(ScheduledVisitServiceContract::class)->overdue($this->doctor->id);

    expect($overdue->pluck('visit.id')->all())->toBe([$late->id]);
});

test('el dashboard muestra la agenda con el teléfono para recordar', function (): void {
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-04', 'reason' => 'Control 15 meses']);
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-01', 'reason' => 'Traer laboratorio']);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('dusk="doctor-agenda"', false)
        ->assertSeeText('Mi agenda de visitas')
        ->assertSeeText('Mañana')
        ->assertSeeText('Control 15 meses')
        ->assertSeeText('Para recordar')
        ->assertSeeText('Traer laboratorio')
        ->assertSeeText('hace 2 días')
        ->assertSee('tel:76543210', false);
});

test('la agenda cambia entre 7, 15 y 30 días', function (): void {
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-12', 'reason' => 'En 9 días']);
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-25', 'reason' => 'En 22 días']);

    Livewire::test('doctor-agenda')
        ->assertDontSee('En 9 días')
        ->assertSee('dusk="agenda-day-2026-10-09"', false)
        ->assertDontSee('dusk="agenda-day-2026-10-10"', false)
        ->call('setRange', 15)
        ->assertSee('En 9 días')
        ->assertDontSee('En 22 días')
        ->call('setRange', 30)
        ->assertSee('En 22 días')
        ->assertSee('dusk="agenda-day-2026-11-01"', false)
        ->call('setRange', 99)
        ->assertSet('days', 7);
});

test('clic en un día filtra la lista y otro clic la vuelve a mostrar completa', function (): void {
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-04', 'reason' => 'Visita del 4']);
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-06', 'reason' => 'Visita del 6']);

    Livewire::test('doctor-agenda')
        ->call('selectDay', '2026-10-06')
        ->assertSee('Visita del 6')
        ->assertDontSee('Visita del 4')
        ->call('selectDay', '2026-10-06')
        ->assertSee('Visita del 4')
        ->assertSee('Visita del 6');
});

test('una visita de hoy a la que ya vino se marca como ya vino', function (): void {
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-03', 'reason' => 'Control']);
    Consultation::factory()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
        'consultation_date' => '2026-10-03 09:30:00',
    ]);

    Livewire::test('doctor-agenda')
        ->assertSee('Ya vino')
        ->assertSee('Hoy');
});

test('programar en plena consulta no marca la visita como cumplida por esa misma consulta', function (): void {
    Consultation::factory()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
        'consultation_date' => '2026-10-03 09:30:00',
    ]);

    // La doctora la programa a las 10:00, durante la consulta de las 09:30.
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'scheduled_for' => '2026-10-04', 'reason' => 'Vuelva mañana', 'created_at' => now()]);

    Livewire::test('doctor-agenda')
        ->assertSee('Vuelva mañana')
        ->assertDontSee('Ya vino');
});

test('un usuario sin perfil de doctor ve las visitas de todos', function (): void {
    agendaVisit(['patient_id' => $this->patient->id, 'doctor_id' => Doctor::factory()->create()->id, 'scheduled_for' => '2026-10-05', 'reason' => 'De otro doctor']);

    $this->actingAs(User::factory()->create(['doctor_id' => null]));

    Livewire::test('doctor-agenda')->assertSee('De otro doctor');
});
