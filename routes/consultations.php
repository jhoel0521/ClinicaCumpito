<?php

use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ConsultationFileController;
use App\Http\Controllers\ConsultationPdfController;
use App\Http\Controllers\LaboratoryRequestController;
use App\Http\Controllers\LaboratoryRequestItemController;
use App\Http\Controllers\PatientVaccineController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PrescriptionItemController;
use App\Http\Controllers\SoapNoteController;
use App\Http\Controllers\VitalSignController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Una consulta digital solo se inicia desde el perfil de un paciente existente.
    Route::post('consultas/iniciar/{patient}', [ConsultationController::class, 'quickStore'])
        ->name('consultas.quick-store');

    Route::livewire('consultas', 'pages::consultas.index')->name('consultas.index');

    Route::resource('consultas', ConsultationController::class)
        ->parameters(['consultas' => 'consulta'])
        ->except(['create', 'store', 'index']);

    Route::get('consultas/{consulta}/archivo', [ConsultationFileController::class, 'serve'])
        ->name('consultas.archivo.serve');

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

    Route::post('consultas/{consulta}/recetas', [PrescriptionController::class, 'store'])
        ->name('consultas.prescriptions.store');
    Route::put('consultas/{consulta}/recetas', [PrescriptionController::class, 'update'])
        ->name('consultas.prescriptions.update');
    Route::delete('consultas/{consulta}/recetas', [PrescriptionController::class, 'destroy'])
        ->name('consultas.prescriptions.destroy');

    Route::post('consultas/{consulta}/recetas/detalles', [PrescriptionItemController::class, 'store'])
        ->name('consultas.prescription-items.store');
    Route::put('consultas/{consulta}/recetas/detalles/{detalleReceta}', [PrescriptionItemController::class, 'update'])
        ->name('consultas.prescription-items.update');
    Route::delete('consultas/{consulta}/recetas/detalles/{detalleReceta}', [PrescriptionItemController::class, 'destroy'])
        ->name('consultas.prescription-items.destroy');

    Route::post('consultas/{consulta}/laboratorios', [LaboratoryRequestController::class, 'store'])
        ->name('consultas.laboratory-requests.store');
    Route::put('consultas/{consulta}/laboratorios/{laboratoryRequest}', [LaboratoryRequestController::class, 'update'])
        ->name('consultas.laboratory-requests.update');
    Route::delete('consultas/{consulta}/laboratorios/{laboratoryRequest}', [LaboratoryRequestController::class, 'destroy'])
        ->name('consultas.laboratory-requests.destroy');

    Route::post('consultas/{consulta}/laboratorios/detalles', [LaboratoryRequestItemController::class, 'store'])
        ->name('consultas.laboratory-request-items.store');
    Route::put('consultas/{consulta}/laboratorios/detalles/{detalleLabor}', [LaboratoryRequestItemController::class, 'update'])
        ->name('consultas.laboratory-request-items.update');
    Route::delete('consultas/{consulta}/laboratorios/detalles/{detalleLabor}', [LaboratoryRequestItemController::class, 'destroy'])
        ->name('consultas.laboratory-request-items.destroy');

    // PDF
    Route::get('consultas/{consulta}/pdf/recetas', [ConsultationPdfController::class, 'allPrescriptions'])
        ->name('consultas.pdf.recetas.all');
    Route::get('consultas/{consulta}/pdf/recetas/{prescription}', [ConsultationPdfController::class, 'singlePrescription'])
        ->name('consultas.pdf.recetas.single');
    Route::get('consultas/{consulta}/pdf/laboratorios', [ConsultationPdfController::class, 'allLaboratory'])
        ->name('consultas.pdf.laboratorio.all');
    Route::get('consultas/{consulta}/pdf/laboratorios/{laboratoryRequest}', [ConsultationPdfController::class, 'singleLaboratory'])
        ->name('consultas.pdf.laboratorio.single');
});
