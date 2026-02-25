<?php

namespace App\Http\Controllers;

use App\Contracts\LaboratoryRequestItemServiceContract;
use App\DTOs\LaboratoryRequestItemDTO;
use App\Http\Requests\StoreLaboratoryRequestItemRequest;
use App\Models\Consultation;
use App\Models\LaboratoryRequestItem;

class LaboratoryRequestItemController extends Controller
{
    public function __construct(private LaboratoryRequestItemServiceContract $service) {}

    public function store(StoreLaboratoryRequestItemRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        if (! $consulta->laboratoryRequest) {
            return back()->withErrors([
                'laboratory_request_items' => 'Debe crear la solicitud de laboratorio antes de registrar detalles.',
            ]);
        }

        try {
            $dto = LaboratoryRequestItemDTO::fromArray($request->validated());
            $this->service->create($consulta->laboratoryRequest->id, $dto);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Detalle de solicitud de laboratorio guardado exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors([
                'laboratory_request_items' => $exception->getMessage(),
            ]);
        }
    }

    public function update(
        StoreLaboratoryRequestItemRequest $request,
        Consultation $consulta,
        LaboratoryRequestItem $detalleLabor
    ): \Illuminate\Http\RedirectResponse {
        if ($detalleLabor->laboratoryRequest?->consultation_id !== $consulta->id) {
            abort(404);
        }

        try {
            $dto = LaboratoryRequestItemDTO::fromArray($request->validated());
            $this->service->update($detalleLabor->id, $dto);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Detalle de solicitud de laboratorio actualizado exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors([
                'laboratory_request_items' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(Consultation $consulta, LaboratoryRequestItem $detalleLabor): \Illuminate\Http\RedirectResponse
    {
        if ($detalleLabor->laboratoryRequest?->consultation_id !== $consulta->id) {
            abort(404);
        }

        try {
            $this->service->delete($detalleLabor->id);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Detalle de solicitud de laboratorio eliminado exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withErrors([
                'laboratory_request_items' => $exception->getMessage(),
            ]);
        }
    }
}
