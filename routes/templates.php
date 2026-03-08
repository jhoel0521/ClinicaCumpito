<?php

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('templates', 'pages::templates.index')->name('templates.index');
    Route::livewire('templates/prescriptions', 'pages::templates.prescription-templates')->name('templates.prescriptions');
    Route::livewire('templates/laboratories', 'pages::templates.laboratory-templates')->name('templates.laboratories');
});
