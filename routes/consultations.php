<?php

use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PatientVaccineController;
use App\Http\Controllers\SoapNoteController;
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

    Route::post('consultas/{consulta}/soap-notes', [SoapNoteController::class, 'store'])
        ->name('consultas.soap-notes.store');
    Route::put('consultas/{consulta}/soap-notes', [SoapNoteController::class, 'update'])
        ->name('consultas.soap-notes.update');
    Route::delete('consultas/{consulta}/soap-notes', [SoapNoteController::class, 'destroy'])
        ->name('consultas.soap-notes.destroy');

    Route::post('consultas/{consulta}/vacunas-paciente', [PatientVaccineController::class, 'store'])
        ->name('consultas.patient-vaccines.store');
    Route::put('consultas/{consulta}/vacunas-paciente/{vacunaPaciente}', [PatientVaccineController::class, 'update'])
        ->name('consultas.patient-vaccines.update');
    Route::delete('consultas/{consulta}/vacunas-paciente/{vacunaPaciente}', [PatientVaccineController::class, 'destroy'])
        ->name('consultas.patient-vaccines.destroy');
});
