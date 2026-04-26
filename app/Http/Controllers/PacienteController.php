<?php

namespace App\Http\Controllers;

use App\DTOs\PacienteDTO;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use App\Models\LaboratoryRequest;
use App\Models\MedicalCondition;
use App\Models\Patient;
use App\Services\PacienteService;
use Illuminate\View\View;

class PacienteController extends Controller
{
    public function __construct(private PacienteService $service) {}

    /**
     * Display a listing of patients.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Patient::class);

        return view('pacientes.index');
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create(): View
    {
        $this->authorize('create', Patient::class);

        $conditions = MedicalCondition::all();

        return view('pacientes.create', compact('conditions'));
    }

    /**
     * Store a newly created patient in database.
     */
    public function store(StorePacienteRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', Patient::class);

        $dto = PacienteDTO::fromArray($request->validated());
        $patient = $this->service->create($dto);

        return redirect()->route('pacientes.show', $patient)
            ->with('success', 'Paciente creado exitosamente.');
    }

    /**
     * Display the specified patient (dashboard).
     */
    public function show(Patient $patient): View
    {
        $this->authorize('view', $patient);

        $patient->load('medicalConditions');

        $recentConsultations = $patient->consultations()
            ->orderByDesc('consultation_date')
            ->with(['vitalSigns', 'soapNote', 'prescriptions.items', 'laboratoryRequests.items', 'doctor'])
            ->limit(3)
            ->get();

        $latestConsultation = $recentConsultations->first();

        $recentConsultasConReceta = $patient->consultations()
            ->whereHas('prescriptions.items')
            ->orderByDesc('consultation_date')
            ->with(['prescriptions.items', 'doctor'])
            ->limit(3)
            ->get();

        $recentLaboratoryRequests = LaboratoryRequest::whereHas(
            'consultation', fn ($q) => $q->where('patient_id', $patient->id)
        )
            ->with(['items', 'consultation.doctor'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $totalConsultaciones = $patient->consultations()->count();

        return view('pacientes.show', compact(
            'patient',
            'latestConsultation',
            'recentConsultations',
            'recentConsultasConReceta',
            'recentLaboratoryRequests',
            'totalConsultaciones',
        ));
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient): View
    {
        $this->authorize('update', $patient);

        $conditions = MedicalCondition::all();
        $patient->load('medicalConditions');

        return view('pacientes.edit', compact('patient', 'conditions'));
    }

    /**
     * Update the specified patient in database.
     */
    public function update(UpdatePacienteRequest $request, Patient $patient): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $patient);

        $dto = PacienteDTO::fromArray($request->validated());
        $patient = $this->service->update($patient->id, $dto);

        return redirect()->route('pacientes.show', $patient)
            ->with('success', 'Paciente actualizado exitosamente.');
    }

    /**
     * Show the quick form for loading old (historical) patient records.
     */
    public function createOld(): View
    {
        $this->authorize('create', Patient::class);

        return view('pacientes.create-old');
    }

    /**
     * Remove the specified patient from database.
     */
    public function destroy(Patient $patient): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $patient);

        $this->service->delete($patient->id);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente eliminado exitosamente.');
    }
}
