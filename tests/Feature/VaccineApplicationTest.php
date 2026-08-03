<?php

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\PatientVaccine;
use App\Models\User;
use App\Models\Vaccine;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    $this->patient = Patient::factory()->create([
        'date_of_birth' => now()->subYears(2)->toDateString(),
    ]);
    $this->consultation = Consultation::factory()->create([
        'patient_id' => $this->patient->id,
        'status' => 'draft',
        'consultation_date' => now(),
    ]);
    $this->vaccine = Vaccine::factory()->create([
        'name' => 'BCG',
        'recommended_age' => 'Al nacer',
        'min_age_months' => 0,
    ]);
});

test('marcar Sí registra la vacuna con la fecha de hoy por defecto', function () {
    Livewire::test('consultation-vaccines', ['consultationId' => $this->consultation->id])
        ->call('applyVaccine', $this->vaccine->id);

    $registro = PatientVaccine::query()->sole();

    expect($registro->vaccine_id)->toBe($this->vaccine->id)
        ->and($registro->applied_at->isToday())->toBeTrue()
        ->and($registro->applied_elsewhere)->toBeFalse();
});

test('marcar Sí con una fecha previa carga el esquema anterior del paciente', function () {
    Livewire::test('consultation-vaccines', ['consultationId' => $this->consultation->id])
        ->set("applyDates.{$this->vaccine->id}", '2024-03-15')
        ->call('applyVaccine', $this->vaccine->id);

    $registro = PatientVaccine::query()->sole();

    expect($registro->applied_at->format('Y-m-d'))->toBe('2024-03-15')
        ->and($registro->applied_elsewhere)->toBeTrue();
});

test('no permite registrar una fecha de aplicación futura', function () {
    Livewire::test('consultation-vaccines', ['consultationId' => $this->consultation->id])
        ->set("applyDates.{$this->vaccine->id}", now()->addMonth()->format('Y-m-d'))
        ->call('applyVaccine', $this->vaccine->id)
        ->assertSet('errorMessage', 'La fecha de aplicación no puede ser futura.');

    expect(PatientVaccine::count())->toBe(0);
});

test('marcar No quita el registro de la vacuna', function () {
    $component = Livewire::test('consultation-vaccines', ['consultationId' => $this->consultation->id])
        ->call('applyVaccine', $this->vaccine->id);

    $registro = PatientVaccine::query()->sole();

    $component->call('removeApplied', $registro->id);

    expect(PatientVaccine::count())->toBe(0);
});
