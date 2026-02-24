<?php

namespace App\Http\Controllers;

use App\Contracts\ConsultationServiceContract;
use App\DTOs\ConsultationDTO;
use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PrescriptionTemplate;
use App\Models\Vaccine;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function __construct(private ConsultationServiceContract $service) {}

    public function index(): View
    {
        $consultations = $this->service->paginate();

        return view('consultas.index', compact('consultations'));
    }

    public function create(): View
    {
        $patients = Patient::query()->orderBy('full_name')->get(['id', 'full_name']);
        $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);

        return view('consultas.create', compact('patients', 'doctors'));
    }

    public function store(StoreConsultationRequest $request): \Illuminate\Http\RedirectResponse
    {
        $dto = ConsultationDTO::fromArray($request->validated());
        $consultation = $this->service->create($dto);

        return redirect()->route('consultas.show', $consultation->id)
            ->with('success', 'Consulta creada exitosamente.');
    }

    public function show(Consultation $consulta): View
    {
        $consulta->load(['patient', 'doctor', 'vitalSigns', 'soapNote', 'prescription.sourceTemplate', 'patientVaccines.vaccine']);
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

    public function edit(Consultation $consulta): View
    {
        $patients = Patient::query()->orderBy('full_name')->get(['id', 'full_name']);
        $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);

        return view('consultas.edit', [
            'consultation' => $consulta,
            'patients' => $patients,
            'doctors' => $doctors,
        ]);
    }

    public function update(UpdateConsultationRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        try {
            $dto = ConsultationDTO::fromArray($request->validated());
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
}
