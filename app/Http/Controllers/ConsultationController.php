<?php

namespace App\Http\Controllers;

use App\Contracts\ConsultationServiceContract;
use App\DTOs\ConsultationDTO;
use App\Http\Requests\UpdateConsultationRequest;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\PrescriptionTemplate;
use App\Models\Vaccine;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function __construct(private ConsultationServiceContract $service) {}

    public function quickStore(Patient $patient): RedirectResponse
    {
        $this->authorize('create', Consultation::class);

        if (! $patient->hasCompleteBasicData()) {
            return redirect()
                ->route('pacientes.edit', $patient)
                ->with('require_complete', true);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $dto = ConsultationDTO::fromArray([
            'patient_id' => $patient->id,
            'doctor_id' => $user->doctor_id,
            'type' => 'digital',
            'status' => 'draft',
            'consultation_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $consultation = $this->service->create($dto);

        return redirect()->route('consultas.show', $consultation->id);
    }

    public function show(Consultation $consulta): View
    {
        $this->authorize('view', $consulta);

        $consulta->load([
            'patient', 'doctor', 'vitalSigns', 'soapNote',
            'prescriptions.items',
            'laboratoryRequests.items',
            'patientVaccines.vaccine',
        ]);
        $vaccines = Vaccine::query()->orderBy('name')->get(['id', 'name']);
        $prescriptionTemplates = PrescriptionTemplate::query()
            ->where('doctor_id', $consulta->doctor_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('consultas.show', [
            'consultation' => $consulta,
            'vaccines' => $vaccines,
            'prescriptionTemplates' => $prescriptionTemplates,
        ]);
    }

    public function edit(Consultation $consulta): RedirectResponse
    {
        $this->authorize('update', $consulta);

        return redirect()->route('consultas.show', $consulta->id);
    }

    public function update(UpdateConsultationRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $consulta);

        try {
            $dto = ConsultationDTO::fromArray(array_merge([
                'patient_id' => $consulta->patient_id,
                'doctor_id' => $consulta->doctor_id,
                'type' => $consulta->getRawOriginal('type'),
            ], $request->validated()));
            $consultation = $this->service->update($consulta->id, $dto);

            return redirect()->route('consultas.show', $consultation->id)
                ->with('success', 'Consulta actualizada exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors([
                'status' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $consulta);

        try {
            $this->service->delete($consulta->id);

            return redirect()->route('consultas.index')
                ->with('success', 'Consulta eliminada exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withErrors([
                'status' => $exception->getMessage(),
            ]);
        }
    }

    public function discardDraft(Consultation $consulta): RedirectResponse
    {
        $this->authorize('discard', $consulta);

        $patientId = $consulta->patient_id;

        try {
            $this->service->discardDraft($consulta->id);

            return redirect()
                ->route('pacientes.show', $patientId)
                ->with('success', 'Borrador de consulta descartado.');
        } catch (\DomainException $exception) {
            return back()->withErrors([
                'status' => $exception->getMessage(),
            ]);
        }
    }
}
