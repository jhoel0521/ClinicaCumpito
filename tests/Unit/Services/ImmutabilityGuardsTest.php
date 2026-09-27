<?php

use App\Contracts\PatientVaccineServiceContract;
use App\Contracts\SoapNoteServiceContract;
use App\Contracts\VitalSignServiceContract;
use App\DTOs\SoapNoteDTO;
use App\DTOs\VitalSignDTO;
use App\Models\Consultation;
use App\Models\PatientVaccine;
use App\Models\SoapNote;
use App\Models\Vaccine;
use App\Models\VitalSign;

test('soap note cannot be written on a finalized consultation', function () {
    $consultation = Consultation::factory()->finalized()->create();

    expect(fn () => app(SoapNoteServiceContract::class)->upsert(
        $consultation->id,
        new SoapNoteDTO(subjective: 'cambio tardío', objective: null, assessment: null, plan: null),
    ))->toThrow(DomainException::class);

    expect(SoapNote::where('consultation_id', $consultation->id)->exists())->toBeFalse();
});

test('soap note is still saved on a draft consultation', function () {
    $consultation = Consultation::factory()->draft()->create();

    app(SoapNoteServiceContract::class)->upsert(
        $consultation->id,
        new SoapNoteDTO(subjective: 'fiebre', objective: null, assessment: null, plan: null),
    );

    expect(SoapNote::where('consultation_id', $consultation->id)->value('subjective'))->toBe('fiebre');
});

test('vital signs cannot be written on a finalized consultation', function () {
    $consultation = Consultation::factory()->finalized()->create();

    expect(fn () => app(VitalSignServiceContract::class)->upsert(
        $consultation->id,
        new VitalSignDTO(weight: 12.5, height: null, head_circumference: null, temperature: null),
    ))->toThrow(DomainException::class);

    expect(VitalSign::where('consultation_id', $consultation->id)->exists())->toBeFalse();
});

test('vaccines registered in a finalized consultation cannot be deleted', function () {
    $consultation = Consultation::factory()->finalized()->create();
    $vaccine = Vaccine::factory()->create();
    $applied = PatientVaccine::factory()->create([
        'patient_id' => $consultation->patient_id,
        'consultation_id' => $consultation->id,
        'vaccine_id' => $vaccine->id,
    ]);

    expect(fn () => app(PatientVaccineServiceContract::class)->delete($applied->id))
        ->toThrow(DomainException::class);

    expect(PatientVaccine::find($applied->id))->not->toBeNull();
});
