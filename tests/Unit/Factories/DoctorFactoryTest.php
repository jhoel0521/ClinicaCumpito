<?php

use App\Models\Doctor;
use Illuminate\Support\Str;

test('doctor factory creates a valid record', function () {
    $doctor = Doctor::factory()->create();

    expect($doctor->id)->not->toBeNull()
        ->and(Str::isUuid($doctor->id))->toBeTrue()
        ->and($doctor->full_name)->not->toBeEmpty()
        ->and($doctor->license_number)->not->toBeEmpty();
});

test('doctor factory can attach a user', function () {
    $doctor = Doctor::factory()->withUser()->create();

    expect($doctor->user_id)->not->toBeNull()
        ->and($doctor->user)->not->toBeNull();
});
