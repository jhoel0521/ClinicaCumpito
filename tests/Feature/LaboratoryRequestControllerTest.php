<?php

use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\User;

describe('LaboratoryRequestController', function () {
    test('usuario autenticado puede guardar solicitud de laboratorio', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);

        $response = $this->actingAs($user)
            ->post(route('consultas.laboratory-requests.store', $consultation->id), [
                'observations' => 'Ayunas mínimo 8 horas.',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('laboratory_requests', [
            'consultation_id' => $consultation->id,
            'observations' => 'Ayunas mínimo 8 horas.',
        ]);
    });

    test('usuario autenticado puede actualizar solicitud de laboratorio', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);
        LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'observations' => 'Observaciones originales.',
        ]);

        $response = $this->actingAs($user)
            ->put(route('consultas.laboratory-requests.update', $consultation->id), [
                'observations' => 'Observaciones actualizadas.',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('laboratory_requests', [
            'consultation_id' => $consultation->id,
            'observations' => 'Observaciones actualizadas.',
        ]);
    });

    test('usuario autenticado puede eliminar solicitud de laboratorio', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);
        LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('consultas.laboratory-requests.destroy', $consultation->id));

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseMissing('laboratory_requests', [
            'consultation_id' => $consultation->id,
            'deleted_at' => null,
        ]);
    });

    test('no permite crear solicitud de laboratorio en consulta finalizada', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'status' => 'finalized',
        ]);

        $response = $this->actingAs($user)
            ->post(route('consultas.laboratory-requests.store', $consultation->id), [
                'observations' => 'Intento de edición bloqueado.',
            ]);

        $response->assertSessionHasErrors('laboratory_request');

        $this->assertDatabaseMissing('laboratory_requests', [
            'consultation_id' => $consultation->id,
        ]);
    });

    test('usuario invitado es redirigido al login', function () {
        $consultation = Consultation::factory()->create();

        $response = $this->post(route('consultas.laboratory-requests.store', $consultation->id), []);

        $response->assertRedirect(route('login'));
    });
});
