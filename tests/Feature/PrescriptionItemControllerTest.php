<?php

use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;

describe('PrescriptionItemController', function () {
    test('usuario autenticado puede guardar detalle de receta', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('consultas.prescription-items.store', $consultation->id), [
                'medication_name' => 'Amoxicilina',
                'dose' => '250 mg',
                'frequency' => 'Cada 8 horas',
                'duration' => '7 días',
                'instructions' => 'Completar tratamiento',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('prescription_items', [
            'medication_name' => 'Amoxicilina',
            'duration' => '7 días',
        ]);
    });

    test('usuario autenticado puede actualizar detalle de receta', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $prescription = Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        $item = PrescriptionItem::factory()->create([
            'prescription_id' => $prescription->id,
            'medication_name' => 'Inicial',
        ]);

        $response = $this->actingAs($user)
            ->put(route('consultas.prescription-items.update', [$consultation->id, $item->id]), [
                'medication_name' => 'Actualizado',
                'dose' => '500 mg',
                'frequency' => 'Cada 12 horas',
                'duration' => '5 días',
                'instructions' => 'Post comida',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('prescription_items', [
            'id' => $item->id,
            'medication_name' => 'Actualizado',
        ]);
    });

    test('falla validacion si medication_name es vacio', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('consultas.prescription-items.store', $consultation->id), [
                'medication_name' => '',
                'dose' => '500 mg',
                'frequency' => 'Cada 12 horas',
                'duration' => '5 días',
            ]);

        $response->assertSessionHasErrors('medication_name');
    });
});
