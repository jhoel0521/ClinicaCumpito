<?php

use App\DTOs\PrescriptionDTO;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Services\PrescriptionService;

describe('PrescriptionService', function () {
    test('createForConsultation crea receta para una consulta', function () {
        $service = new PrescriptionService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);

        $dto = PrescriptionDTO::fromArray([
            'observations' => 'Control en 72 horas.',
        ]);

        $prescription = $service->createForConsultation($consultation->id, $dto);

        expect($prescription)->toBeInstanceOf(Prescription::class)
            ->and($prescription->consultation_id)->toBe($consultation->id)
            ->and($prescription->observations)->toBe('Control en 72 horas.');
    });

    test('createForConsultation falla en consulta finalizada', function () {
        $service = new PrescriptionService;
        $consultation = Consultation::factory()->create([
            'status' => 'finalized',
        ]);

        $dto = PrescriptionDTO::fromArray([
            'observations' => 'No debería guardar.',
        ]);

        $this->expectException(DomainException::class);
        $service->createForConsultation($consultation->id, $dto);
    });

    test('delete elimina receta existente', function () {
        $service = new PrescriptionService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);
        $prescription = Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $deleted = $service->delete($prescription->id);

        expect($deleted)->toBeTrue();
        expect(Prescription::find($prescription->id))->toBeNull();
    });
});
