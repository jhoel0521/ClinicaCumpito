<?php

use App\DTOs\PrescriptionDTO;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionTemplate;
use App\Services\PrescriptionService;

describe('PrescriptionService', function () {
    test('upsert crea receta para una consulta', function () {
        $service = new PrescriptionService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);
        $template = PrescriptionTemplate::factory()->create([
            'doctor_id' => $consultation->doctor_id,
        ]);

        $dto = PrescriptionDTO::fromArray([
            'source_template_id' => $template->id,
            'observations' => 'Control en 72 horas.',
        ]);

        $prescription = $service->upsert($consultation->id, $dto);

        expect($prescription)->toBeInstanceOf(Prescription::class)
            ->and($prescription->consultation_id)->toBe($consultation->id)
            ->and($prescription->source_template_id)->toBe($template->id);
    });

    test('upsert falla en consulta finalizada', function () {
        $service = new PrescriptionService;
        $consultation = Consultation::factory()->create([
            'status' => 'finalized',
        ]);

        $dto = PrescriptionDTO::fromArray([
            'observations' => 'No debería guardar.',
        ]);

        $this->expectException(DomainException::class);
        $service->upsert($consultation->id, $dto);
    });

    test('deleteByConsultation elimina receta existente', function () {
        $service = new PrescriptionService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);
        $prescription = Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $deleted = $service->deleteByConsultation($prescription->consultation_id);

        expect($deleted)->toBeTrue();
        expect(Prescription::find($prescription->id))->toBeNull();
    });
});
