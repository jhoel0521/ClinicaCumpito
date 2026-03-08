<?php

use App\Models\Consultation;
use App\Models\PatientVaccine;
use App\Models\User;
use App\Models\Vaccine;

describe('PatientVaccineController', function () {
    test('usuario autenticado puede guardar vacuna aplicada', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create();
        $vaccine = Vaccine::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('consultas.patient-vaccines.store', $consultation->id), [
                'vaccine_id' => $vaccine->id,
                'applied_at' => now()->subDay()->toDateTimeString(),
                'dose_number' => 1,
                'notes' => 'Aplicación intramuscular.',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('patient_vaccines', [
            'consultation_id' => $consultation->id,
            'vaccine_id' => $vaccine->id,
            'dose_number' => 1,
        ]);
    });

    test('usuario autenticado puede actualizar vacuna aplicada', function () {
        $user = User::factory()->create();
        $patientVaccine = PatientVaccine::factory()->create([
            'dose_number' => 1,
        ]);
        $newVaccine = Vaccine::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('consultas.patient-vaccines.update', [$patientVaccine->consultation_id, $patientVaccine->id]), [
                'vaccine_id' => $newVaccine->id,
                'applied_at' => now()->toDateTimeString(),
                'dose_number' => 2,
                'notes' => 'Refuerzo aplicado.',
            ]);

        $response->assertRedirect(route('consultas.show', $patientVaccine->consultation_id));

        $this->assertDatabaseHas('patient_vaccines', [
            'id' => $patientVaccine->id,
            'vaccine_id' => $newVaccine->id,
            'dose_number' => 2,
        ]);
    });

    test('falla validacion cuando vaccine_id no existe', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('consultas.patient-vaccines.store', $consultation->id), [
                'vaccine_id' => (string) str()->uuid(),
                'applied_at' => now()->toDateTimeString(),
            ]);

        $response->assertSessionHasErrors('vaccine_id');
    });
});
