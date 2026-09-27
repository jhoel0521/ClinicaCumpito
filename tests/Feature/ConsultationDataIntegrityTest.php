<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientVaccine;
use App\Models\User;
use App\Models\Vaccine;
use Livewire\Livewire;

beforeEach(function () {
    $this->doctor = Doctor::factory()->create();
    $this->user = User::factory()->create(['doctor_id' => $this->doctor->id]);
    $this->patient = Patient::factory()->create([
        'date_of_birth' => now()->subMonths(8)->toDateString(),
        'gender' => 'F',
    ]);
});

it('reutiliza el borrador del día si se toca dos veces "Nueva Consulta"', function () {
    $this->actingAs($this->user)->post(route('consultas.quick-store', $this->patient));
    $this->actingAs($this->user)->post(route('consultas.quick-store', $this->patient));

    expect(Consultation::where('patient_id', $this->patient->id)->count())->toBe(1);
});

it('crea un borrador nuevo si el anterior ya fue finalizado', function () {
    Consultation::factory()->finalized()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_date' => now()->subHour(),
    ]);

    $this->actingAs($this->user)->post(route('consultas.quick-store', $this->patient));

    expect(Consultation::where('patient_id', $this->patient->id)->count())->toBe(2);
});

it('abre una consulta sin doctor asignado sin error', function () {
    $admin = User::factory()->admin()->create();
    $consultation = Consultation::factory()->scanned()->create(['patient_id' => $this->patient->id]);

    $this->actingAs($admin)
        ->get(route('consultas.show', $consultation->id))
        ->assertOk()
        ->assertSee('Sin doctor asignado');
});

it('rechaza una fecha de consulta anterior al nacimiento', function () {
    $consultation = Consultation::factory()->draft()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
    ]);

    Livewire::actingAs($this->user)
        ->test('consultation-header', ['consultationId' => $consultation->id])
        ->set('consultation_date', now()->subYears(2)->format('Y-m-d\TH:i'))
        ->call('saveDate')
        ->assertHasErrors('consultation_date');

    expect($consultation->fresh()->consultation_date->isAfter(now()->subYear()))->toBeTrue();
});

it('no finaliza una consulta con fecha futura', function () {
    $consultation = Consultation::factory()->draft()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
    ]);

    Livewire::actingAs($this->user)
        ->test('consultation-header', ['consultationId' => $consultation->id])
        ->set('consultation_date', now()->addDays(3)->format('Y-m-d\TH:i'))
        ->call('finalize')
        ->assertHasErrors('consultation_date');

    expect($consultation->fresh()->status->isDraft())->toBeTrue();
});

it('rechaza una vacuna con fecha anterior al nacimiento', function () {
    $consultation = Consultation::factory()->draft()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_date' => now(),
    ]);
    $vaccine = Vaccine::factory()->create();

    Livewire::actingAs($this->user)
        ->test('consultation-vaccines', ['consultationId' => $consultation->id])
        ->set('applyDates.'.$vaccine->id, now()->subYears(2)->toDateString())
        ->call('applyVaccine', $vaccine->id);

    expect(PatientVaccine::where('patient_id', $this->patient->id)->exists())->toBeFalse();
});
