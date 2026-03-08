<?php

use App\Models\Prescription;

test('prescription factory creates a valid record', function () {
    $prescription = Prescription::factory()->create();

    expect($prescription->id)->not->toBeNull()
        ->and($prescription->consultation_id)->not->toBeNull();
});
