<?php

namespace App\Http\Controllers;

use App\Contracts\PatientVaccineServiceContract;
use App\DTOs\PatientVaccineDTO;
use App\Http\Requests\StorePatientVaccineRequest;
use App\Models\Consultation;
use App\Models\PatientVaccine;

class PatientVaccineController extends Controller
{
    public function __construct(private PatientVaccineServiceContract $service) {}

    public function store(StorePatientVaccineRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        $dto = PatientVaccineDTO::fromArray($request->validated());
        $this->service->create($consulta->id, $dto);

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Vacuna aplicada registrada exitosamente.');
    }

    public function update(
        StorePatientVaccineRequest $request,
        Consultation $consulta,
        PatientVaccine $vacunaPaciente
    ): \Illuminate\Http\RedirectResponse {
        if ($vacunaPaciente->consultation_id !== $consulta->id) {
            abort(404);
        }

        $dto = PatientVaccineDTO::fromArray($request->validated());
        $this->service->update($vacunaPaciente->id, $dto);

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Vacuna aplicada actualizada exitosamente.');
    }

    public function destroy(Consultation $consulta, PatientVaccine $vacunaPaciente): \Illuminate\Http\RedirectResponse
    {
        if ($vacunaPaciente->consultation_id !== $consulta->id) {
            abort(404);
        }

        $this->service->delete($vacunaPaciente->id);

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Vacuna aplicada eliminada exitosamente.');
    }
}
