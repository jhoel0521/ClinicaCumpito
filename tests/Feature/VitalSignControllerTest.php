<?php

use App\Models\Consultation;
use App\Models\User;
use App\Models\VitalSign;

describe('VitalSignController', function () {
    test('usuario autenticado puede guardar signos vitales', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('consultas.vital-signs.store', $consultation->id), [
                'weight' => 14.2,
                'height' => 90.1,
                'head_circumference' => 48.3,
                'temperature' => 36.7,
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('vital_signs', [
            'consultation_id' => $consultation->id,
            'weight' => 14.2,
        ]);
    });

    test('usuario autenticado puede actualizar signos vitales', function () {
        $user = User::factory()->create();
        $vitalSign = VitalSign::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('consultas.vital-signs.update', $vitalSign->consultation_id), [
                'weight' => 15.5,
                'height' => 95,
                'head_circumference' => 49,
                'temperature' => 37.1,
            ]);

        $response->assertRedirect(route('consultas.show', $vitalSign->consultation_id));

        $this->assertDatabaseHas('vital_signs', [
            'id' => $vitalSign->id,
            'weight' => 15.5,
        ]);
    });

    test('falla validacion con temperatura fuera de rango', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('consultas.vital-signs.store', $consultation->id), [
                'temperature' => 45,
            ]);

        $response->assertSessionHasErrors('temperature');
    });
});
