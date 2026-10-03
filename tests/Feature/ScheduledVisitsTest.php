<?php

use App\Contracts\ScheduledVisitServiceContract;
use App\DTOs\ScheduledVisitDTO;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\ScheduledVisit;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

/*
 * Seguimiento del paciente: controles 0–12 meses, calendario compacto y
 * visitas programadas ("control extra" y "programar visita" son lo mismo).
 */
beforeEach(function (): void {
    Carbon::setTestNow('2026-08-07 10:00:00');

    $this->doctor = Doctor::factory()->create();
    $this->user = User::factory()->create(['doctor_id' => $this->doctor->id]);
    $this->actingAs($this->user);

    // Nació el 7 de enero de 2026: hoy tiene 7 meses.
    $this->patient = Patient::factory()->create(['date_of_birth' => '2026-01-07']);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function consultationOn(Patient $patient, Doctor $doctor, string $date, string $status = 'finalized'): Consultation
{
    return Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => $status,
        'consultation_date' => $date,
    ]);
}

// ── Servicio ─────────────────────────────────────────────────────────────

test('programar una visita guarda doctor, autor y queda auditada', function (): void {
    $visit = app(ScheduledVisitServiceContract::class)->schedule(
        $this->patient->id,
        ScheduledVisitDTO::fromArray(['scheduled_for' => '2026-08-11', 'reason' => '  Traer laboratorio ']),
    );

    expect($visit->reason)->toBe('Traer laboratorio')
        ->and($visit->scheduled_for->format('Y-m-d'))->toBe('2026-08-11')
        ->and($visit->doctor_id)->toBe($this->doctor->id)
        ->and($visit->created_by_user_id)->toBe($this->user->id)
        ->and(AuditLog::query()->where('auditable_id', $visit->id)->where('action', 'created')->exists())->toBeTrue();
});

test('si quien programa no es doctor, la visita queda a nombre del doctor responsable del paciente', function (): void {
    $responsible = Doctor::factory()->create();
    $patient = Patient::factory()->create(['responsible_doctor_id' => $responsible->id]);
    $this->actingAs(User::factory()->create(['doctor_id' => null]));

    $visit = app(ScheduledVisitServiceContract::class)->schedule(
        $patient->id,
        ScheduledVisitDTO::fromArray(['scheduled_for' => '2026-08-11', 'reason' => 'Control']),
    );

    expect($visit->doctor_id)->toBe($responsible->id);
});

test('no se programa en fecha pasada, inválida o sin motivo', function (array $data): void {
    app(ScheduledVisitServiceContract::class)->schedule($this->patient->id, ScheduledVisitDTO::fromArray($data));
})->with([
    'pasada' => [['scheduled_for' => '2026-08-06', 'reason' => 'Control']],
    'inválida' => [['scheduled_for' => '2026-02-30', 'reason' => 'Control']],
    'sin motivo' => [['scheduled_for' => '2026-08-10', 'reason' => '   ']],
])->throws(DomainException::class);

test('el resumen cuenta a tiempo, en el mes, no vino y pendientes', function (): void {
    ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-06-01']);
    ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-05-01']);
    ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-04-01']);
    ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-08-20']);

    consultationOn($this->patient, $this->doctor, '2026-06-02 09:00:00'); // a tiempo
    consultationOn($this->patient, $this->doctor, '2026-05-20 09:00:00', 'draft'); // en el mes (borrador también cuenta)

    expect(app(ScheduledVisitServiceContract::class)->summaryForPatient($this->patient->id))
        ->toBe(['on_time' => 1, 'late' => 1, 'missed' => 1, 'pending' => 1]);
});

test('solo se pueden quitar visitas pendientes', function (): void {
    $pending = ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-08-20']);
    $done = ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-06-01']);
    consultationOn($this->patient, $this->doctor, '2026-06-01 09:00:00');

    $service = app(ScheduledVisitServiceContract::class);

    expect($service->delete($pending->id))->toBeTrue()
        ->and(fn () => $service->delete($done->id))->toThrow(DomainException::class)
        ->and(ScheduledVisit::query()->whereKey($done->id)->exists())->toBeTrue();
});

// ── Perfil: componente de seguimiento ───────────────────────────────────

test('los controles van de recién nacido a 12 meses y distinguen hecho, faltante y futuro', function (): void {
    consultationOn($this->patient, $this->doctor, '2026-02-07 09:00:00'); // 1 mes
    consultationOn($this->patient, $this->doctor, '2026-08-07 09:00:00', 'saved'); // 7 meses
    consultationOn($this->patient, $this->doctor, '2026-03-07 09:00:00', 'draft'); // borrador: no marca control

    Livewire::test('patient-follow-up', ['patient' => $this->patient])
        ->assertSee('Controles mensuales 0–12 meses')
        ->assertSee('2 de 13 controles')
        ->assertSee('dusk="control-month-1"', false)
        ->assertSee('dusk="control-month-7"', false)
        ->assertSee('dusk="control-missing-2"', false)
        ->assertSee('dusk="control-missing-0"', false)
        ->assertSee('dusk="control-future-8"', false)
        ->assertSee('dusk="control-future-12"', false)
        ->assertDontSee('dusk="control-future-13"', false)
        ->assertDontSee('control-month-13', false);
});

test('cada control enlaza a la consulta más reciente de ese mes', function (): void {
    consultationOn($this->patient, $this->doctor, '2026-08-01 09:00:00');
    $latest = consultationOn($this->patient, $this->doctor, '2026-08-06 09:00:00');

    Livewire::test('patient-follow-up', ['patient' => $this->patient])
        ->assertSee(route('consultas.show', $latest->id), false);
});

test('sin fecha de nacimiento no muestra controles pero sí calendario y visitas', function (): void {
    $patient = Patient::factory()->create(['date_of_birth' => null]);

    Livewire::test('patient-follow-up', ['patient' => $patient])
        ->assertDontSee('Controles mensuales')
        ->assertSee('dusk="consultation-calendar"', false)
        ->assertSee('Programar visita');

    expect(Consultation::count())->toBe(0);
});

test('el calendario marca consultas y navega entre meses sin duplicar', function (): void {
    consultationOn($this->patient, $this->doctor, '2026-08-10 09:00:00');
    consultationOn($this->patient, $this->doctor, '2026-07-20 09:00:00', 'saved');

    Livewire::test('patient-follow-up', ['patient' => $this->patient])
        ->assertSee('dusk="calendar-consultation-10"', false)
        ->assertDontSee('dusk="calendar-consultation-20"', false)
        ->call('prevMonth')
        ->assertSee('dusk="calendar-consultation-20"', false)
        ->assertDontSee('dusk="calendar-consultation-10"', false)
        ->call('nextMonth')
        ->call('nextMonth')
        ->assertDontSee('dusk="calendar-consultation-10"', false);

    expect(Consultation::count())->toBe(2);
});

test('programar desde un día del calendario actualiza calendario y próximas visitas a la vez', function (): void {
    Livewire::test('patient-follow-up', ['patient' => $this->patient])
        ->call('openSchedule', '2026-08-11')
        ->assertSet('showScheduleForm', true)
        ->assertSet('newDate', '2026-08-11')
        ->set('newReason', 'Traer laboratorio')
        ->call('scheduleVisit')
        ->assertSet('showScheduleForm', false)
        ->assertSee('dusk="calendar-visit-11"', false)
        ->assertSee('11/08/2026')
        ->assertSee('Traer laboratorio');

    expect(ScheduledVisit::query()->where('patient_id', $this->patient->id)->count())->toBe(1);
});

test('un día pasado del calendario no prellena la fecha', function (): void {
    Livewire::test('patient-follow-up', ['patient' => $this->patient])
        ->call('openSchedule', '2026-08-01')
        ->assertSet('newDate', '');
});

test('el error de programación se muestra en el formulario', function (): void {
    Livewire::test('patient-follow-up', ['patient' => $this->patient])
        ->call('openSchedule')
        ->set('newDate', '2026-08-12')
        ->set('newReason', '')
        ->call('scheduleVisit')
        ->assertSet('showScheduleForm', true)
        ->assertSee('Indica el motivo de la visita');
});

test('el perfil muestra como máximo 3 próximas visitas y enlaza a ver todas', function (): void {
    foreach (['2026-08-10', '2026-08-20', '2026-09-01', '2026-10-01', '2026-11-01'] as $date) {
        ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => $date, 'reason' => "Visita {$date}"]);
    }

    Livewire::test('patient-follow-up', ['patient' => $this->patient])
        ->assertSee('Visita 2026-08-10')
        ->assertSee('Visita 2026-09-01')
        ->assertDontSee('Visita 2026-10-01')
        ->assertSee('+ 2 más')
        ->assertSee(route('pacientes.visitas', $this->patient), false);
});

test('el card de cumplimiento muestra los conteos', function (): void {
    ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-06-01']);
    ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-04-01']);
    consultationOn($this->patient, $this->doctor, '2026-06-01 09:00:00');

    $html = Livewire::test('patient-follow-up', ['patient' => $this->patient])->html();

    expect($html)->toMatch('/dusk="summary-on-time">\s*1\s*</')
        ->and($html)->toMatch('/dusk="summary-late">\s*0\s*</')
        ->and($html)->toMatch('/dusk="summary-missed">\s*1\s*</');
});

test('quitar una visita desde el perfil la elimina', function (): void {
    $visit = ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-08-20', 'reason' => 'Control']);

    Livewire::test('patient-follow-up', ['patient' => $this->patient])
        ->call('deleteVisit', $visit->id)
        ->assertSee('Sin visitas programadas.');

    expect(ScheduledVisit::query()->whereKey($visit->id)->exists())->toBeFalse();
});

test('no se puede quitar desde el perfil una visita de otro paciente', function (): void {
    $other = ScheduledVisit::factory()->create(['scheduled_for' => '2026-08-20']);

    Livewire::test('patient-follow-up', ['patient' => $this->patient])
        ->call('deleteVisit', $other->id);

    expect(ScheduledVisit::query()->whereKey($other->id)->exists())->toBeTrue();
});

test('el perfil del paciente muestra el seguimiento en una sola sección', function (): void {
    $this->get(route('pacientes.show', $this->patient))
        ->assertOk()
        ->assertSee('dusk="patient-follow-up"', false)
        ->assertSee('dusk="monthly-controls"', false)
        ->assertSee('dusk="consultation-calendar"', false)
        ->assertSeeText('Calendario de consultas')
        ->assertDontSeeText('0–24 meses');
});

// ── Pantalla completa de visitas ────────────────────────────────────────

test('la pantalla de visitas lista todas, filtra por estado y pagina', function (): void {
    for ($i = 1; $i <= 17; $i++) {
        ScheduledVisit::factory()->create([
            'patient_id' => $this->patient->id,
            'scheduled_for' => Carbon::parse('2026-08-10')->addDays($i)->format('Y-m-d'),
            'reason' => "Futura {$i}",
        ]);
    }
    ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-04-01', 'reason' => 'Faltó abril']);

    $this->get(route('pacientes.visitas', $this->patient))
        ->assertOk()
        ->assertSeeText('Visitas de '.$this->patient->full_name)
        ->assertSee('dusk="visits-table"', false);

    $page = Livewire::test('pages::pacientes.visitas', ['patient' => $this->patient]);
    expect(substr_count($page->html(), 'dusk="visit-row"'))->toBe(15);

    $page->set('filter', 'missed')
        ->assertSee('Faltó abril')
        ->assertDontSee('Futura 1<', false);
    expect(substr_count($page->html(), 'dusk="visit-row"'))->toBe(1);
});

test('desde la pantalla de visitas se puede quitar una pendiente', function (): void {
    $visit = ScheduledVisit::factory()->create(['patient_id' => $this->patient->id, 'scheduled_for' => '2026-08-20']);

    Livewire::test('pages::pacientes.visitas', ['patient' => $this->patient])
        ->call('deleteVisit', $visit->id);

    expect(ScheduledVisit::query()->whereKey($visit->id)->exists())->toBeFalse();
});
