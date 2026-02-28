<?php

use App\Http\Controllers\PacienteController;
use App\Models\Patient;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rutas de Pacientes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('pacientes/create-old', [PacienteController::class, 'createOld'])
        ->name('pacientes.create-old');
    Route::resource('pacientes', PacienteController::class);
    Route::model('paciente', Patient::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/catalogs.php';
require __DIR__.'/templates.php';
require __DIR__.'/consultations.php';
