<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\Prescription;
use App\Services\ClinicalDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClinicalDocumentController extends Controller
{
    public function __construct(private ClinicalDocumentService $documents) {}

    public function recetaPreview(Prescription $prescription): View|Response|RedirectResponse
    {
        $this->authorizeConsultation($prescription->consultation);

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
        $this->authorizeConsultation($prescription->consultation);

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

    public function ordenPreview(Request $request, LaboratoryRequest $laboratoryRequest): View|Response|RedirectResponse
    {
        $this->authorizeConsultation($laboratoryRequest->consultation);

        [$category, $exam] = $this->ordenFilter($request);
        $doc = $this->documents->ordenLaboratorio($laboratoryRequest, $category, $exam);

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
            'editUrl' => route('consultas.show', $laboratoryRequest->consultation_id).'#laboratorio',
            'downloadUrl' => $doc->overflow ? null : route('documentos.laboratorios.pdf', array_filter([
                'laboratoryRequest' => $laboratoryRequest,
                'categoria' => $category,
                'examen' => $exam,
            ])),
        ]);
    }

    public function ordenPdf(Request $request, LaboratoryRequest $laboratoryRequest): Response|RedirectResponse
    {
        $this->authorizeConsultation($laboratoryRequest->consultation);

        [$category, $exam] = $this->ordenFilter($request);
        $doc = $this->documents->ordenLaboratorio($laboratoryRequest, $category, $exam);

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

    /**
     * Filtro opcional para imprimir solo una categoría o un examen de la orden.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function ordenFilter(Request $request): array
    {
        $category = trim((string) $request->query('categoria', ''));
        $exam = trim((string) $request->query('examen', ''));

        return [$category !== '' ? $category : null, $exam !== '' ? $exam : null];
    }

    private function authorizeConsultation(?Consultation $consultation): void
    {
        if (! $consultation instanceof Consultation) {
            abort(404);
        }

        $this->authorize('view', $consultation);
    }
}
