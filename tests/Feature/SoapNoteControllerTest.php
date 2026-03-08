<?php

use App\Models\Consultation;
use App\Models\SoapNote;
use App\Models\User;

describe('SoapNoteController', function () {
    test('usuario autenticado puede guardar nota soap', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('consultas.soap-notes.store', $consultation->id), [
                'subjective' => 'Dolor de garganta',
                'objective' => 'Amígdalas hipertróficas',
                'assessment' => 'Faringitis',
                'plan' => 'Tratamiento sintomático',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('soap_notes', [
            'consultation_id' => $consultation->id,
            'assessment' => 'Faringitis',
        ]);
    });

    test('usuario autenticado puede actualizar nota soap', function () {
        $user = User::factory()->create();
        $soapNote = SoapNote::factory()->create([
            'assessment' => 'Diagnóstico inicial',
        ]);

        $response = $this->actingAs($user)
            ->put(route('consultas.soap-notes.update', $soapNote->consultation_id), [
                'subjective' => 'Persistencia de síntomas',
                'objective' => 'Hallazgos clínicos actualizados',
                'assessment' => 'Diagnóstico actualizado',
                'plan' => 'Ajuste terapéutico',
            ]);

        $response->assertRedirect(route('consultas.show', $soapNote->consultation_id));

        $this->assertDatabaseHas('soap_notes', [
            'id' => $soapNote->id,
            'assessment' => 'Diagnóstico actualizado',
        ]);
    });

    test('falla validacion cuando subjective excede tamaño maximo', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('consultas.soap-notes.store', $consultation->id), [
                'subjective' => str_repeat('a', 5001),
            ]);

        $response->assertSessionHasErrors('subjective');
    });
});
