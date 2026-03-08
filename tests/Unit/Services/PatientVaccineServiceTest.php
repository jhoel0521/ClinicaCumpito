<?php

use App\DTOs\PatientVaccineDTO;
use App\Models\Consultation;
use App\Models\PatientVaccine;
use App\Models\Vaccine;
use App\Services\PatientVaccineService;

describe('PatientVaccineService', function () {
    test('create registra una vacuna aplicada', function () {
        $service = new PatientVaccineService;
        $consultation = Consultation::factory()->create();
        $vaccine = Vaccine::factory()->create();

        $dto = PatientVaccineDTO::fromArray([
            'vaccine_id' => $vaccine->id,
            'applied_at' => now()->subDay()->toDateTimeString(),
            'application_site' => 'Centro externo',
            'dose_number' => 1,
            'notes' => 'Primera dosis aplicada sin reacción.',
        ]);

        $patientVaccine = $service->create($consultation->id, $dto);

        expect($patientVaccine)->toBeInstanceOf(PatientVaccine::class)
            ->and($patientVaccine->consultation_id)->toBe($consultation->id)
            ->and($patientVaccine->patient_id)->toBe($consultation->patient_id)
            ->and($patientVaccine->vaccine_id)->toBe($vaccine->id)
            ->and($patientVaccine->dose_number)->toBe(1);
    });

    test('update modifica una vacuna aplicada existente', function () {
        $service = new PatientVaccineService;
        $patientVaccine = PatientVaccine::factory()->create([
            'dose_number' => 1,
        ]);
        $newVaccine = Vaccine::factory()->create();

        $dto = PatientVaccineDTO::fromArray([
            'vaccine_id' => $newVaccine->id,
            'applied_at' => now()->toDateTimeString(),
            'applied_by_doctor_id' => null,
            'application_site' => 'Vacunatorio municipal',
            'dose_number' => 2,
            'notes' => 'Segunda dosis aplicada.',
        ]);

        $updated = $service->update($patientVaccine->id, $dto);

        expect($updated->vaccine_id)->toBe($newVaccine->id)
            ->and($updated->dose_number)->toBe(2)
            ->and($updated->notes)->toBe('Segunda dosis aplicada.');
    });

    test('delete elimina una vacuna aplicada', function () {
        $service = new PatientVaccineService;
        $patientVaccine = PatientVaccine::factory()->create();

        $deleted = $service->delete($patientVaccine->id);

        expect($deleted)->toBeTrue();
        expect(PatientVaccine::find($patientVaccine->id))->toBeNull();
    });

    test('listByConsultation retorna vacunas de una consulta', function () {
        $service = new PatientVaccineService;
        $consultation = Consultation::factory()->create();
        PatientVaccine::factory()->count(2)->create([
            'consultation_id' => $consultation->id,
        ]);

        PatientVaccine::factory()->create();

        $list = $service->listByConsultation($consultation->id);

        expect($list)->toHaveCount(2);
    });
});
