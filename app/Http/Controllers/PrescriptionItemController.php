<?php

namespace App\Http\Controllers;

use App\Contracts\PrescriptionItemServiceContract;
use App\DTOs\PrescriptionItemDTO;
use App\Http\Requests\StorePrescriptionItemRequest;
use App\Models\Consultation;
use App\Models\PrescriptionItem;

class PrescriptionItemController extends Controller
{
    public function __construct(private PrescriptionItemServiceContract $service) {}

    public function store(StorePrescriptionItemRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        if (! $consulta->prescription) {
            return back()->withErrors([
                'prescription_items' => 'Debe crear la receta antes de registrar detalles.',
            ]);
        }

        try {
            $dto = PrescriptionItemDTO::fromArray($request->validated());
            $this->service->create($consulta->prescription->id, $dto);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Detalle de receta guardado exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors([
                'prescription_items' => $exception->getMessage(),
            ]);
        }
    }

    public function update(
        StorePrescriptionItemRequest $request,
        Consultation $consulta,
        PrescriptionItem $detalleReceta
    ): \Illuminate\Http\RedirectResponse {
        if ($detalleReceta->prescription?->consultation_id !== $consulta->id) {
            abort(404);
        }

        try {
            $dto = PrescriptionItemDTO::fromArray($request->validated());
            $this->service->update($detalleReceta->id, $dto);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Detalle de receta actualizado exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors([
                'prescription_items' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(Consultation $consulta, PrescriptionItem $detalleReceta): \Illuminate\Http\RedirectResponse
    {
        if ($detalleReceta->prescription?->consultation_id !== $consulta->id) {
            abort(404);
        }

        try {
            $this->service->delete($detalleReceta->id);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Detalle de receta eliminado exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withErrors([
                'prescription_items' => $exception->getMessage(),
            ]);
        }
    }
}
