<?php

use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\User;

describe('LaboratoryRequestItemController', function () {
    test('usuario autenticado puede guardar detalle de solicitud de laboratorio', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('consultas.laboratory-request-items.store', $consultation->id), [
                'exam_name' => 'Hemograma completo',
                'indications' => 'Ayunas de 8 horas',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('laboratory_request_items', [
            'exam_name' => 'Hemograma completo',
        ]);
    });

    test('usuario autenticado puede actualizar detalle de solicitud de laboratorio', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
            'exam_name' => 'Inicial',
        ]);

        $response = $this->actingAs($user)
            ->put(route('consultas.laboratory-request-items.update', [$consultation->id, $item->id]), [
                'exam_name' => 'Glucosa en ayunas',
                'indications' => 'Sin azúcar 12 horas antes',
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('laboratory_request_items', [
            'id' => $item->id,
            'exam_name' => 'Glucosa en ayunas',
        ]);
    });

    test('usuario autenticado puede eliminar detalle de solicitud de laboratorio', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('consultas.laboratory-request-items.destroy', [$consultation->id, $item->id]));

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseMissing('laboratory_request_items', ['id' => $item->id]);
    });

    test('falla validacion si exam_name esta vacio', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('consultas.laboratory-request-items.store', $consultation->id), [
                'exam_name' => '',
            ]);

        $response->assertSessionHasErrors('exam_name');
    });

    test('retorna 404 si el item no pertenece a la consulta', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $otherConsultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $otherConsultation->id,
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('consultas.laboratory-request-items.update', [$consultation->id, $item->id]), [
                'exam_name' => 'Intento inválido',
            ]);

        $response->assertNotFound();
    });
});
