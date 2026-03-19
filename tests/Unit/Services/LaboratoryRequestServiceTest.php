<?php

use App\DTOs\LaboratoryRequestDTO;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Services\LaboratoryRequestService;

describe('LaboratoryRequestService', function () {
    test('createForConsultation crea solicitud de laboratorio para una consulta', function () {
        $service = new LaboratoryRequestService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);

        $dto = LaboratoryRequestDTO::fromArray([
            'observations' => 'Ayunas de 8 horas.',
            'status' => 'pending',
        ]);

        $request = $service->createForConsultation($consultation->id, $dto);

        expect($request)->toBeInstanceOf(LaboratoryRequest::class)
            ->and($request->consultation_id)->toBe($consultation->id)
            ->and($request->observations)->toBe('Ayunas de 8 horas.');
    });

    test('createForConsultation falla en consulta finalizada', function () {
        $service = new LaboratoryRequestService;
        $consultation = Consultation::factory()->create([
            'status' => 'finalized',
        ]);

        $dto = LaboratoryRequestDTO::fromArray([
            'observations' => 'No debería guardar.',
        ]);

        $this->expectException(DomainException::class);
        $service->createForConsultation($consultation->id, $dto);
    });

    test('update actualiza solicitud existente', function () {
        $service = new LaboratoryRequestService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);
        $existing = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'observations' => 'Inicial',
        ]);

        $dto = LaboratoryRequestDTO::fromArray([
            'observations' => 'Actualizado',
        ]);

        $request = $service->update($existing->id, $dto);

        expect($request->observations)->toBe('Actualizado');
        expect(LaboratoryRequest::where('consultation_id', $consultation->id)->count())->toBe(1);
    });

    test('findByConsultation retorna solicitud existente', function () {
        $service = new LaboratoryRequestService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);
        $request = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $found = $service->findByConsultation($consultation->id);

        expect($found)->toBeInstanceOf(LaboratoryRequest::class)
            ->and($found->id)->toBe($request->id);
    });

    test('delete elimina solicitud existente', function () {
        $service = new LaboratoryRequestService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);
        $request = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $deleted = $service->delete($request->id);

        expect($deleted)->toBeTrue();
        expect(LaboratoryRequest::find($request->id))->toBeNull();
    });

    test('delete falla en consulta finalizada', function () {
        $service = new LaboratoryRequestService;
        $consultation = Consultation::factory()->create([
            'status' => 'finalized',
        ]);
        $request = LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $this->expectException(DomainException::class);
        $service->delete($request->id);
    });
});
