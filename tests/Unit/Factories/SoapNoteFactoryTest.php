<?php

use App\Models\SoapNote;
use Illuminate\Support\Str;

test('soap note factory creates a valid record', function () {
    $soapNote = SoapNote::factory()->create();

    expect($soapNote->id)->not->toBeNull()
        ->and(Str::isUuid($soapNote->id))->toBeTrue()
        ->and($soapNote->consultation_id)->not->toBeNull();
});
