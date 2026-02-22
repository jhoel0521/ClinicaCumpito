<?php

use App\Models\VitalSign;
use Illuminate\Support\Str;

test('vital sign factory creates a valid record', function () {
    $vitalSign = VitalSign::factory()->create();

    expect($vitalSign->id)->not->toBeNull()
        ->and(Str::isUuid($vitalSign->id))->toBeTrue()
        ->and($vitalSign->consultation_id)->not->toBeNull();
});
