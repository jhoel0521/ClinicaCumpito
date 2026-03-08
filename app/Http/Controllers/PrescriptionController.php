<?php

namespace App\Http\Controllers;

use App\Contracts\PrescriptionServiceContract;
use App\DTOs\PrescriptionDTO;
use App\Http\Requests\StorePrescriptionRequest;
use App\Models\Consultation;

class PrescriptionController extends Controller
{
    public function __construct(private PrescriptionServiceContract $service) {}

    public function store(StorePrescriptionRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        try {
            $dto = PrescriptionDTO::fromArray($request->validated());
            $this->service->upsert($consulta->id, $dto);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Receta guardada exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors([
                'prescription' => $exception->getMessage(),
            ]);
        }
    }

    public function update(StorePrescriptionRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        try {
            $dto = PrescriptionDTO::fromArray($request->validated());
            $this->service->upsert($consulta->id, $dto);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Receta actualizada exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors([
                'prescription' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->service->deleteByConsultation($consulta->id);

            return redirect()->route('consultas.show', $consulta->id)
                ->with('success', 'Receta eliminada exitosamente.');
        } catch (\DomainException $exception) {
            return back()->withErrors([
                'prescription' => $exception->getMessage(),
            ]);
        }
    }
}
