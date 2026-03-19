<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ConsultationPdfController extends Controller
{
    public function allPrescriptions(Consultation $consulta): Response
    {
        $consulta->load(['patient', 'doctor', 'prescriptions.items']);
        $clinic = ClinicSetting::current();

        $pdf = Pdf::loadView('pdf.prescription-all', [
            'consultation' => $consulta,
            'clinic' => $clinic,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("recetas-{$consulta->id}.pdf");
    }

    public function singlePrescription(Consultation $consulta, Prescription $prescription): Response
    {
        $consulta->load(['patient', 'doctor']);
        $prescription->load('items');
        $clinic = ClinicSetting::current();

        $pdf = Pdf::loadView('pdf.prescription-single', [
            'consultation' => $consulta,
            'prescription' => $prescription,
            'clinic' => $clinic,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("receta-{$prescription->id}.pdf");
    }

    public function allLaboratory(Consultation $consulta): Response
    {
        $consulta->load(['patient', 'doctor', 'laboratoryRequests.items']);
        $clinic = ClinicSetting::current();

        $pdf = Pdf::loadView('pdf.laboratory-all', [
            'consultation' => $consulta,
            'clinic' => $clinic,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("laboratorios-{$consulta->id}.pdf");
    }

    public function singleLaboratory(Consultation $consulta, LaboratoryRequest $laboratoryRequest): Response
    {
        $consulta->load(['patient', 'doctor']);
        $laboratoryRequest->load('items');
        $clinic = ClinicSetting::current();

        $pdf = Pdf::loadView('pdf.laboratory-single', [
            'consultation' => $consulta,
            'laboratoryRequest' => $laboratoryRequest,
            'clinic' => $clinic,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("laboratorio-{$laboratoryRequest->id}.pdf");
    }
}
