<?php

use App\Models\Consultation;
use Illuminate\Support\Str;

test('consultation factory creates a valid record', function () {
    $consultation = Consultation::factory()->create();

    expect($consultation->id)->not->toBeNull()
        ->and(Str::isUuid($consultation->id))->toBeTrue()
        ->and($consultation->patient_id)->not->toBeNull()
        ->and($consultation->doctor_id)->not->toBeNull();
});
