<?php

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Livewire\Livewire;

test('la prueba del talón se registra al crear el paciente', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('pacientes.store'), [
            'full_name' => 'Thiago Méndez',
            'date_of_birth' => '2022-08-04',
            'gender' => 'M',
            'heel_prick_done' => '1',
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('patients', [
        'full_name' => 'Thiago Méndez',
        'heel_prick_done' => true,
    ]);
});

test('el encabezado de consulta no muestra ni edita la prueba del talón', function () {
    $patient = Patient::factory()->create(['heel_prick_done' => true]);
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'draft',
    ]);

    Livewire::test('consultation-header', ['consultationId' => $consultation->id])
        ->assertDontSee('Prueba del talón');

    expect($patient->fresh()->heel_prick_done)->toBeTrue();
});

test('el encabezado muestra descartar borrador solo para consultas en borrador', function () {
    $draft = Consultation::factory()->draft()->create();
    $saved = Consultation::factory()->create(['status' => 'saved']);

    Livewire::test('consultation-header', ['consultationId' => $draft->id])
        ->assertSee('Descartar borrador');

    Livewire::test('consultation-header', ['consultationId' => $saved->id])
        ->assertDontSee('Descartar borrador');
});
