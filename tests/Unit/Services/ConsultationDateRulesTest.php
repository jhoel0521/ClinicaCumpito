<?php

use App\Contracts\ConsultationServiceContract;
use App\DTOs\ConsultationDTO;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;

beforeEach(function () {
    $this->patient = Patient::factory()->create(['date_of_birth' => now()->subYears(2)->toDateString()]);
    $this->doctor = Doctor::factory()->create();
});

function consultationDto(string $patientId, ?string $doctorId, string $date): ConsultationDTO
{
    return ConsultationDTO::fromArray([
        'patient_id' => $patientId,
        'doctor_id' => $doctorId,
        'type' => 'digital',
        'status' => 'draft',
        'consultation_date' => $date,
    ]);
}

test('rejects a consultation dated before the patient was born', function () {
    $date = now()->subYears(3)->format('Y-m-d H:i:s');

    expect(fn () => app(ConsultationServiceContract::class)->create(consultationDto($this->patient->id, $this->doctor->id, $date)))
        ->toThrow(DomainException::class, 'anterior al nacimiento');
});

test('rejects a consultation dated in the future', function () {
    $date = now()->addDays(2)->format('Y-m-d H:i:s');

    expect(fn () => app(ConsultationServiceContract::class)->create(consultationDto($this->patient->id, $this->doctor->id, $date)))
        ->toThrow(DomainException::class, 'futura');
});

test('rejects moving an existing consultation before birth', function () {
    $consultation = Consultation::factory()->draft()->create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
    ]);

    expect(fn () => app(ConsultationServiceContract::class)->update(
        $consultation->id,
        consultationDto($this->patient->id, $this->doctor->id, now()->subYears(5)->format('Y-m-d H:i:s')),
    ))->toThrow(DomainException::class);
});

test('accepts a consultation without doctor (scanned history)', function () {
    $consultation = app(ConsultationServiceContract::class)->create(
        consultationDto($this->patient->id, null, now()->subMonth()->format('Y-m-d H:i:s')),
    );

    expect($consultation->doctor_id)->toBeNull();
});

test('dto keeps a missing doctor as null instead of an empty string', function () {
    $dto = consultationDto($this->patient->id, null, now()->format('Y-m-d H:i:s'));

    expect($dto->doctor_id)->toBeNull()
        ->and($dto->toArray()['doctor_id'])->toBeNull();
});
