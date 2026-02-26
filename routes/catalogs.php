<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::livewire('catalogs', 'pages::catalogs.index')->name('catalogs.index');
    Route::livewire('catalogs/laboratories', 'pages::catalogs.laboratories')->name('catalogs.laboratories');
    Route::livewire('catalogs/medications', 'pages::catalogs.medications')->name('catalogs.medications');
    Route::livewire('catalogs/vaccines', 'pages::catalogs.vaccines')->name('catalogs.vaccines');
    Route::livewire('catalogs/oms-graficas', 'pages::catalogs.oms-graficas')->name('catalogs.oms-graficas');
});
