<?php

use App\DTOs\LaboratoryRequestItemDTO;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Services\LaboratoryRequestItemService;

describe('LaboratoryRequestItemService', function () {
    test('create registra detalle de solicitud de laboratorio', function () {
        $service = new LaboratoryRequestItemService;
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $dto = LaboratoryRequestItemDTO::fromArray([
            'exam_name' => 'Hemograma completo',
            'indications' => 'Ayunas de 8 horas',
        ]);

        $item = $service->create($labRequest->id, $dto);

        expect($item)->toBeInstanceOf(LaboratoryRequestItem::class)
            ->and($item->exam_name)->toBe('Hemograma completo');
    });

    test('create falla en consulta finalizada', function () {
        $service = new LaboratoryRequestItemService;
        $consultation = Consultation::factory()->create(['status' => 'finalized']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $dto = LaboratoryRequestItemDTO::fromArray([
            'exam_name' => 'Glucosa',
        ]);

        $this->expectException(DomainException::class);
        $service->create($labRequest->id, $dto);
    });

    test('update modifica detalle de solicitud de laboratorio', function () {
        $service = new LaboratoryRequestItemService;
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
            'exam_name' => 'Inicial',
        ]);

        $dto = LaboratoryRequestItemDTO::fromArray([
            'exam_name' => 'Urocultivo',
            'indications' => 'Muestra de orina de primera hora',
        ]);

        $updated = $service->update($item->id, $dto);

        expect($updated->exam_name)->toBe('Urocultivo')
            ->and($updated->indications)->toBe('Muestra de orina de primera hora');
    });

    test('listByRequest retorna ítems de la solicitud', function () {
        $service = new LaboratoryRequestItemService;
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        LaboratoryRequestItem::factory()->count(3)->create([
            'laboratory_request_id' => $labRequest->id,
        ]);

        $items = $service->listByRequest($labRequest->id);

        expect($items)->toHaveCount(3);
    });

    test('delete elimina detalle de solicitud de laboratorio', function () {
        $service = new LaboratoryRequestItemService;
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);

        $deleted = $service->delete($item->id);

        expect($deleted)->toBeTrue();
        expect(LaboratoryRequestItem::find($item->id))->toBeNull();
    });
});
