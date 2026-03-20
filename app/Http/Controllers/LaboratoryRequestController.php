<?php

namespace App\Http\Controllers;

use App\Contracts\LaboratoryRequestServiceContract;
use App\DTOs\LaboratoryRequestDTO;
use App\Http\Requests\StoreLaboratoryRequestRequest;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;

class LaboratoryRequestController extends Controller
{
    public function __construct(private LaboratoryRequestServiceContract $service) {}

    public function store(StoreLaboratoryRequestRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        try {
            $dto = LaboratoryRequestDTO::fromArray($request->validated());
            $this->service->createForConsultation($consulta->id, $dto);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Solicitud de laboratorio guardada exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors([
                'laboratory_request' => $exception->getMessage(),
            ]);
        }
    }

    public function update(StoreLaboratoryRequestRequest $request, Consultation $consulta, LaboratoryRequest $laboratoryRequest): \Illuminate\Http\RedirectResponse
    {
        try {
            abort_if($laboratoryRequest->consultation_id !== $consulta->id, 404);
            $dto = LaboratoryRequestDTO::fromArray($request->validated());
            $this->service->update($laboratoryRequest->id, $dto);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Solicitud de laboratorio actualizada exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors([
                'laboratory_request' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(Consultation $consulta, LaboratoryRequest $laboratoryRequest): \Illuminate\Http\RedirectResponse
    {
        try {
            abort_if($laboratoryRequest->consultation_id !== $consulta->id, 404);
            $this->service->delete($laboratoryRequest->id);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Solicitud de laboratorio eliminada exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withErrors([
                'laboratory_request' => $exception->getMessage(),
            ]);
        }
    }
}
