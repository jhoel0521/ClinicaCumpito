<?php

use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\User;

describe('PrescriptionController', function () {
    test('usuario autenticado puede guardar receta', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);

        $response = $this->actingAs($user)
            ->post(route('consultas.prescriptions.store', $consultation->id), [
                'observations' => 'Indicaciones generales de receta.',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('prescriptions', [
            'consultation_id' => $consultation->id,
            'observations' => 'Indicaciones generales de receta.',
        ]);
    });

    test('usuario autenticado puede actualizar receta', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);
        Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('consultas.prescriptions.update', $consultation->id), [
                'observations' => 'Observaciones actualizadas.',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('prescriptions', [
            'consultation_id' => $consultation->id,
            'observations' => 'Observaciones actualizadas.',
        ]);
    });

    test('no permite crear receta en consulta finalizada', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'status' => 'finalized',
        ]);

        $response = $this->actingAs($user)
            ->post(route('consultas.prescriptions.store', $consultation->id), [
                'observations' => 'Intento de edición bloqueado.',
            ]);

        $response->assertSessionHasErrors('prescription');

        $this->assertDatabaseMissing('prescriptions', [
            'consultation_id' => $consultation->id,
        ]);
    });
});
