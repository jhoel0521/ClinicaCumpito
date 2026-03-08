<?php

use App\DTOs\PrescriptionItemDTO;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Services\PrescriptionItemService;

describe('PrescriptionItemService', function () {
    test('create registra detalle de receta', function () {
        $service = new PrescriptionItemService;
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $prescription = Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $dto = PrescriptionItemDTO::fromArray([
            'medication_name' => 'Paracetamol',
            'dose' => '5 ml',
            'frequency' => 'Cada 8 horas',
            'duration' => '3 días',
            'instructions' => 'Después de alimentos',
        ]);

        $item = $service->create($prescription->id, $dto);

        expect($item)->toBeInstanceOf(PrescriptionItem::class)
            ->and($item->medication_name)->toBe('Paracetamol');
    });

    test('update modifica detalle de receta', function () {
        $service = new PrescriptionItemService;
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $prescription = Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        $item = PrescriptionItem::factory()->create([
            'prescription_id' => $prescription->id,
        ]);

        $dto = PrescriptionItemDTO::fromArray([
            'medication_name' => 'Ibuprofeno',
            'dose' => '10 ml',
            'frequency' => 'Cada 12 horas',
            'duration' => '5 días',
            'instructions' => 'Con abundante agua',
        ]);

        $updated = $service->update($item->id, $dto);

        expect($updated->medication_name)->toBe('Ibuprofeno')
            ->and($updated->duration)->toBe('5 días');
    });

    test('delete elimina detalle de receta', function () {
        $service = new PrescriptionItemService;
        $consultation = Consultation::factory()->create(['status' => 'saved']);
        $prescription = Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);
        $item = PrescriptionItem::factory()->create([
            'prescription_id' => $prescription->id,
        ]);

        $deleted = $service->delete($item->id);

        expect($deleted)->toBeTrue();
        expect(PrescriptionItem::find($item->id))->toBeNull();
    });
});
