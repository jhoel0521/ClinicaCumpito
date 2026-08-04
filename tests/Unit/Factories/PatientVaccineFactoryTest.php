<?php

use App\Models\Patient;
use App\Models\PatientVaccine;

test('patient vaccine factory creates a valid record', function () {
    $patientVaccine = PatientVaccine::factory()->create();

    expect($patientVaccine->id)->not->toBeNull()
        ->and($patientVaccine->patient_id)->not->toBeNull()
        ->and($patientVaccine->consultation_id)->not->toBeNull()
        ->and($patientVaccine->vaccine_id)->not->toBeNull()
        ->and($patientVaccine->applied_at)->not->toBeNull();
});

test('un paciente expone sus vacunas aplicadas', function () {
    $patient = Patient::factory()->create();
    $patientVaccine = PatientVaccine::factory()->create(['patient_id' => $patient->id]);

    expect($patient->patientVaccines)->toHaveCount(1)
        ->and($patient->patientVaccines->sole()->is($patientVaccine))->toBeTrue();
});
