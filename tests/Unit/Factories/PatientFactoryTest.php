<?php

use App\Models\Patient;
use Illuminate\Support\Str;

test('patient factory creates a valid record', function () {
    $patient = Patient::factory()->create();

    expect($patient->id)->not->toBeNull()
        ->and(Str::isUuid($patient->id))->toBeTrue()
        ->and($patient->full_name)->not->toBeEmpty()
        ->and($patient->responsible_doctor_id)->not->toBeNull();
});

test('patient factory can attach a user', function () {
    $patient = Patient::factory()->withUser()->create();

    expect($patient->user_id)->not->toBeNull()
        ->and($patient->user)->not->toBeNull();
});
