<?php

use App\DTOs\ConsultationDTO;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\ConsultationService;

describe('ConsultationService', function () {
    test('create puede registrar una consulta', function () {
        $service = new ConsultationService;
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $dto = ConsultationDTO::fromArray([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'type' => 'digital',
            'status' => 'saved',
            'consultation_date' => now()->format('Y-m-d H:i:s'),
            'pending_transcription' => true,
        ]);

        $consultation = $service->create($dto);

        expect($consultation)->toBeInstanceOf(Consultation::class)
            ->and($consultation->patient_id)->toBe($patient->id)
            ->and($consultation->doctor_id)->toBe($doctor->id)
            ->and($consultation->pending_transcription)->toBeTrue();
    });

    test('update puede modificar una consulta no finalizada', function () {
        $service = new ConsultationService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
            'type' => 'digital',
        ]);

        $dto = ConsultationDTO::fromArray([
            'patient_id' => $consultation->patient_id,
            'doctor_id' => $consultation->doctor_id,
            'type' => 'manual',
            'status' => 'saved',
            'consultation_date' => now()->addDay()->format('Y-m-d H:i:s'),
            'pending_transcription' => false,
        ]);

        $updated = $service->update($consultation->id, $dto);

        expect($updated->type->value())->toBe('manual');
    });

    test('update falla cuando consulta esta finalizada', function () {
        $service = new ConsultationService;
        $consultation = Consultation::factory()->create([
            'status' => 'finalized',
        ]);

        $dto = ConsultationDTO::fromArray([
            'patient_id' => $consultation->patient_id,
            'doctor_id' => $consultation->doctor_id,
            'type' => 'digital',
            'status' => 'saved',
            'consultation_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $service->update($consultation->id, $dto);
    })->throws(DomainException::class);

    test('delete elimina consulta no finalizada', function () {
        $service = new ConsultationService;
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
        ]);

        $deleted = $service->delete($consultation->id);

        expect($deleted)->toBeTrue();
        expect(Consultation::find($consultation->id))->toBeNull();
    });
});
