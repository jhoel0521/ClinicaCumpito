<?php

use App\Models\Consultation;
use App\Models\LaboratoryItemResult;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Services\LaboratoryItemResultService;

describe('LaboratoryItemResultService', function () {
    test('create registra el resultado asociado a la consulta de la orden', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'pending',
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);

        $result = $service->create($item->id, [
            'value' => '12.5',
            'report_text' => 'Normal',
            'is_abnormal' => false,
        ]);

        expect($result)->toBeInstanceOf(LaboratoryItemResult::class)
            ->and($result->laboratory_request_item_id)->toBe($item->id)
            ->and($result->consultation_id)->toBe($consultation->id)
            ->and($result->value)->toBe('12.5')
            ->and($result->report_text)->toBe('Normal');
    });

    test('create permite sobrescribir la consulta asociada', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $other = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'pending',
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);

        $result = $service->create($item->id, ['value' => '5'], $other->id);

        expect($result->consultation_id)->toBe($other->id);
    });

    test('create falla si la orden ya está recibida', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'received',
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);

        expect(fn () => $service->create($item->id, ['value' => '1']))
            ->toThrow(DomainException::class);
    });

    test('delete elimina un resultado dentro de la ventana de 3 días', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'pending',
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);
        $result = LaboratoryItemResult::factory()->create([
            'laboratory_request_item_id' => $item->id,
            'consultation_id' => $consultation->id,
        ]);

        $deleted = $service->delete($result->id);

        expect($deleted)->toBeTrue()
            ->and(LaboratoryItemResult::find($result->id))->toBeNull();
    });

    test('delete falla si la orden tiene más de 3 días', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'pending',
            'created_at' => now()->subDays(5),
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);
        $result = LaboratoryItemResult::factory()->create([
            'laboratory_request_item_id' => $item->id,
            'consultation_id' => $consultation->id,
        ]);

        expect(fn () => $service->delete($result->id))
            ->toThrow(DomainException::class, '3 días');
    });

    test('delete falla si la orden ya está recibida', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'received',
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);
        $result = LaboratoryItemResult::factory()->create([
            'laboratory_request_item_id' => $item->id,
            'consultation_id' => $consultation->id,
        ]);

        expect(fn () => $service->delete($result->id))
            ->toThrow(DomainException::class);
    });

    test('update modifica el valor y el informe de un resultado dentro de la ventana de 3 días', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'pending',
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);
        $result = LaboratoryItemResult::factory()->create([
            'laboratory_request_item_id' => $item->id,
            'consultation_id' => $consultation->id,
            'value' => '10.0',
            'is_abnormal' => false,
        ]);

        $updated = $service->update($result->id, [
            'value' => '11.5',
            'report_text' => 'Corregido tras revisión',
            'is_abnormal' => true,
        ]);

        expect($updated->value)->toBe('11.5')
            ->and($updated->report_text)->toBe('Corregido tras revisión')
            ->and($updated->is_abnormal)->toBeTrue();
    });

    test('update falla fuera de la ventana de 3 días', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'pending',
            'created_at' => now()->subDays(5),
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);
        $result = LaboratoryItemResult::factory()->create([
            'laboratory_request_item_id' => $item->id,
            'consultation_id' => $consultation->id,
        ]);

        expect(fn () => $service->update($result->id, ['value' => '9.0']))
            ->toThrow(DomainException::class, '3 días');
    });

    test('update falla si la orden ya está recibida', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'received',
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);
        $result = LaboratoryItemResult::factory()->create([
            'laboratory_request_item_id' => $item->id,
            'consultation_id' => $consultation->id,
        ]);

        expect(fn () => $service->update($result->id, ['value' => '9.0']))
            ->toThrow(DomainException::class);
    });

    test('listByRequest retorna los resultados de la orden', function () {
        $service = app(LaboratoryItemResultService::class);
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $labRequest = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'status' => 'pending',
        ]);
        $item = LaboratoryRequestItem::factory()->create([
            'laboratory_request_id' => $labRequest->id,
        ]);
        LaboratoryItemResult::factory()->count(2)->create([
            'laboratory_request_item_id' => $item->id,
            'consultation_id' => $consultation->id,
        ]);

        $results = $service->listByRequest($labRequest->id);

        expect($results)->toHaveCount(2);
    });
});
