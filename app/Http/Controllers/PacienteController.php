<?php

namespace App\Http\Controllers;

use App\DTOs\PacienteDTO;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
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
        $patients = Patient::with(['user', 'medicalConditions'])->paginate(15);

        return view('pacientes.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create(): View
    {
        $conditions = MedicalCondition::all();

        return view('pacientes.create', compact('conditions'));
    }

    /**
     * Store a newly created patient in database.
     */
    public function store(StorePacienteRequest $request)
    {
        $dto = PacienteDTO::fromArray($request->validated());
        $patient = $this->service->create($dto);

        return redirect()->route('pacientes.show', $patient->id)
            ->with('success', 'Paciente creado exitosamente.');
    }

    /**
     * Display the specified patient (dashboard).
     */
    public function show(Patient $patient): View
    {
        $patient->load(['user', 'medicalConditions', 'consultations']);

        return view('pacientes.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient): View
    {
        $conditions = MedicalCondition::all();
        $patient->load('medicalConditions');

        return view('pacientes.edit', compact('patient', 'conditions'));
    }

    /**
     * Update the specified patient in database.
     */
    public function update(UpdatePacienteRequest $request, Patient $patient)
    {
        $dto = PacienteDTO::fromArray($request->validated());
        $patient = $this->service->update($patient->id, $dto);

        return redirect()->route('pacientes.show', $patient->id)
            ->with('success', 'Paciente actualizado exitosamente.');
    }

    /**
     * Remove the specified patient from database.
     */
    public function destroy(Patient $patient)
    {
        $this->service->delete($patient->id);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente eliminado exitosamente.');
    }
}
