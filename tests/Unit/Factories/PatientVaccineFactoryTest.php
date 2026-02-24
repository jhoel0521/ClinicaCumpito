<?php

use App\Models\PatientVaccine;

test('patient vaccine factory creates a valid record', function () {
    $patientVaccine = PatientVaccine::factory()->create();

    expect($patientVaccine->id)->not->toBeNull()
        ->and($patientVaccine->consultation_id)->not->toBeNull()
        ->and($patientVaccine->vaccine_id)->not->toBeNull()
        ->and($patientVaccine->applied_at)->not->toBeNull();
});
