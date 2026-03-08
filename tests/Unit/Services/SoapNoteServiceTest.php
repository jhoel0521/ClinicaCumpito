<?php

use App\DTOs\SoapNoteDTO;
use App\Models\Consultation;
use App\Models\SoapNote;
use App\Services\SoapNoteService;

describe('SoapNoteService', function () {
    test('upsert crea nota soap para una consulta', function () {
        $service = new SoapNoteService;
        $consultation = Consultation::factory()->create();

        $dto = SoapNoteDTO::fromArray([
            'subjective' => 'Paciente con tos y fiebre',
            'objective' => 'Temperatura 38.2 y faringe eritematosa',
            'assessment' => 'Probable infección respiratoria alta',
            'plan' => 'Control en 48 horas',
        ]);

        $soap = $service->upsert($consultation->id, $dto);

        expect($soap)->toBeInstanceOf(SoapNote::class)
            ->and($soap->consultation_id)->toBe($consultation->id)
            ->and($soap->assessment)->toBe('Probable infección respiratoria alta');
    });

    test('upsert actualiza nota soap existente', function () {
        $service = new SoapNoteService;
        $consultation = Consultation::factory()->create();
        SoapNote::factory()->create([
            'consultation_id' => $consultation->id,
            'assessment' => 'Diagnóstico previo',
        ]);

        $dto = SoapNoteDTO::fromArray([
            'subjective' => 'Dolor abdominal',
            'objective' => 'Sin signos de irritación peritoneal',
            'assessment' => 'Gastritis aguda',
            'plan' => 'Dieta blanda e hidratación',
        ]);

        $soap = $service->upsert($consultation->id, $dto);

        expect($soap->assessment)->toBe('Gastritis aguda');
    });

    test('deleteByConsultation elimina nota soap existente', function () {
        $service = new SoapNoteService;
        $soapNote = SoapNote::factory()->create();

        $deleted = $service->deleteByConsultation($soapNote->consultation_id);

        expect($deleted)->toBeTrue();
        expect(SoapNote::find($soapNote->id))->toBeNull();
    });
});
