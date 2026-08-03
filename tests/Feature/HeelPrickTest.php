<?php

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('la consulta permite registrar si el paciente realizó la prueba del talón', function () {
    $patient = Patient::factory()->create(['heel_prick_done' => null]);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'draft',
    ]);

    Livewire::test('consultation-header', ['consultationId' => $consultation->id])
        ->assertSet('heelPrickDone', null)
        ->call('saveHeelPrick', true)
        ->assertSet('heelPrickDone', true)
        ->call('saveHeelPrick', false)
        ->assertSet('heelPrickDone', false);

    expect($patient->fresh()->heel_prick_done)->toBeFalse();
});

test('la prueba del talón no se edita con la consulta finalizada', function () {
    $patient = Patient::factory()->create(['heel_prick_done' => true]);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'finalized',
    ]);

    Livewire::test('consultation-header', ['consultationId' => $consultation->id])
        ->assertSet('heelPrickDone', true)
        ->call('saveHeelPrick', false)
        ->assertSet('heelPrickDone', true);

    expect($patient->fresh()->heel_prick_done)->toBeTrue();
});
