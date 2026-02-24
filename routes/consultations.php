<?php

use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\VitalSignController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('consultas', ConsultationController::class)->parameters([
        'consultas' => 'consulta',
    ]);

    Route::post('consultas/{consulta}/signos-vitales', [VitalSignController::class, 'store'])
        ->name('consultas.vital-signs.store');
    Route::put('consultas/{consulta}/signos-vitales', [VitalSignController::class, 'update'])
        ->name('consultas.vital-signs.update');
    Route::delete('consultas/{consulta}/signos-vitales', [VitalSignController::class, 'destroy'])
        ->name('consultas.vital-signs.destroy');
});
