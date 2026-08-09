<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryRequest;
use App\Models\Prescription;
use App\Services\ClinicalDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class ClinicalDocumentController extends Controller
{
    public function __construct(private ClinicalDocumentService $documents) {}

    public function recetaPreview(Prescription $prescription): View|Response|RedirectResponse
    {
        $doc = $this->documents->receta($prescription);

        if ($doc->errors !== []) {
            return redirect()
                ->route('consultas.show', $prescription->consultation_id)
                ->withErrors(['documento' => implode(' ', $doc->errors)]);
        }

        return view('documents.preview', [
            'doc' => $doc,
            'documentView' => 'documents.receta',
            'editUrl' => route('consultas.show', $prescription->consultation_id).'#receta',
            'downloadUrl' => $doc->overflow ? null : route('documentos.recetas.pdf', $prescription),
        ]);
    }

    public function recetaPdf(Prescription $prescription): Response|RedirectResponse
    {
        $doc = $this->documents->receta($prescription);

        if (! $doc->isValid()) {
            return redirect()
                ->route('consultas.show', $prescription->consultation_id)
                ->withErrors(['documento' => implode(' ', $doc->errors)]);
        }

        $pdf = Pdf::loadView('documents.receta', ['doc' => $doc])
            ->setPaper($doc->paper->toDompdf(), 'mm');

        return $pdf->stream($doc->fileName());
    }

    public function ordenPreview(LaboratoryRequest $laboratoryRequest): View|Response|RedirectResponse
    {
        $doc = $this->documents->ordenLaboratorio($laboratoryRequest);

        $consultation = $laboratoryRequest->consultation;

        if ($doc->errors !== []) {
            return redirect()
                ->route('pacientes.laboratorios.show', [
                    $consultation?->patient_id,
                    $laboratoryRequest,
                ])
                ->withErrors(['documento' => implode(' ', $doc->errors)]);
        }

        return view('documents.preview', [
            'doc' => $doc,
            'documentView' => 'documents.orden-laboratorio',
            'editUrl' => route('pacientes.laboratorios.show', [
                $consultation?->patient_id,
                $laboratoryRequest,
            ]),
            'downloadUrl' => $doc->overflow ? null : route('documentos.laboratorios.pdf', $laboratoryRequest),
        ]);
    }

    public function ordenPdf(LaboratoryRequest $laboratoryRequest): Response|RedirectResponse
    {
        $doc = $this->documents->ordenLaboratorio($laboratoryRequest);

        $consultation = $laboratoryRequest->consultation;

        if (! $doc->isValid()) {
            return redirect()
                ->route('pacientes.laboratorios.show', [
                    $consultation?->patient_id,
                    $laboratoryRequest,
                ])
                ->withErrors(['documento' => implode(' ', $doc->errors)]);
        }

        $pdf = Pdf::loadView('documents.orden-laboratorio', ['doc' => $doc])
            ->setPaper($doc->paper->toDompdf(), 'mm');

        return $pdf->stream($doc->fileName());
    }
}
