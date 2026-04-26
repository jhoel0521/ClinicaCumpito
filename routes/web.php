<?php

use App\Http\Controllers\PacienteController;
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
    Route::livewire('pacientes/{patient}/recetas', 'pages::pacientes.recetas')
        ->name('pacientes.recetas');
    Route::livewire('pacientes/{patient}/laboratorios', 'pages::pacientes.laboratorios')
        ->name('pacientes.laboratorios');
    Route::livewire('pacientes/{patient}/vacunas', 'pages::pacientes.vacunas')
        ->name('pacientes.vacunas');
    Route::livewire('pacientes/{patient}/feed', 'pages::pacientes.historia-feed')
        ->name('pacientes.feed');
    Route::resource('pacientes', PacienteController::class)
        ->parameters(['pacientes' => 'patient']);

    // Laboratorios
    Route::livewire('laboratorios/{laboratorio}', 'pages::laboratorios.detalle')
        ->name('laboratorios.show');
    Route::livewire('pacientes/{patient}/laboratorios/{laboratorio}', 'pages::laboratorios.detalle')
        ->name('pacientes.laboratorios.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/consultations.php';
