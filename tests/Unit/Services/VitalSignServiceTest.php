<?php

use App\DTOs\VitalSignDTO;
use App\Models\Consultation;
use App\Models\VitalSign;
use App\Services\VitalSignService;

describe('VitalSignService', function () {
    test('upsert crea signos vitales para una consulta', function () {
        $service = new VitalSignService;
        $consultation = Consultation::factory()->create();

        $dto = VitalSignDTO::fromArray([
            'weight' => 12.5,
            'height' => 82.4,
            'head_circumference' => 45.2,
            'temperature' => 36.8,
        ]);

        $vitalSign = $service->upsert($consultation->id, $dto);

        expect($vitalSign)->toBeInstanceOf(VitalSign::class)
            ->and($vitalSign->consultation_id)->toBe($consultation->id)
            ->and($vitalSign->weight->value())->toBe(12.5);
    });

    test('upsert actualiza signos vitales existentes', function () {
        $service = new VitalSignService;
        $consultation = Consultation::factory()->create();
        VitalSign::factory()->create([
            'consultation_id' => $consultation->id,
            'weight' => 10,
        ]);

        $dto = VitalSignDTO::fromArray([
            'weight' => 11.2,
            'height' => 81,
            'head_circumference' => 44,
            'temperature' => 37,
        ]);

        $vitalSign = $service->upsert($consultation->id, $dto);

        expect($vitalSign->weight->value())->toBe(11.2);
    });

    test('deleteByConsultation elimina signos vitales existentes', function () {
        $service = new VitalSignService;
        $vitalSign = VitalSign::factory()->create();

        $deleted = $service->deleteByConsultation($vitalSign->consultation_id);

        expect($deleted)->toBeTrue();
        expect(VitalSign::find($vitalSign->id))->toBeNull();
    });
});
