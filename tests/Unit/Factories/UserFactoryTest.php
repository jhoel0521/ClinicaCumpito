<?php

use App\Models\User;
use Illuminate\Support\Str;

test('user factory creates a valid record', function () {
    $user = User::factory()->create();

    expect($user->id)->not->toBeNull()
        ->and(Str::isUuid($user->id))->toBeTrue()
        ->and($user->email)->not->toBeEmpty()
        ->and($user->phone_number)->not->toBeNull();
});
