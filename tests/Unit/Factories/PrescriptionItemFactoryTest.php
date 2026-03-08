<?php

use App\Models\PrescriptionItem;

test('prescription item factory creates a valid record', function () {
    $item = PrescriptionItem::factory()->create();

    expect($item->id)->not->toBeNull()
        ->and($item->prescription_id)->not->toBeNull()
        ->and($item->medication_name)->not->toBe('');
});
