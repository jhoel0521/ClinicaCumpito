<?php

namespace App\Http\Controllers;

use App\Contracts\VitalSignServiceContract;
use App\DTOs\VitalSignDTO;
use App\Http\Requests\StoreVitalSignRequest;
use App\Models\Consultation;

class VitalSignController extends Controller
{
    public function __construct(private VitalSignServiceContract $service) {}

    public function store(StoreVitalSignRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        $dto = VitalSignDTO::fromArray($request->validated());
        try {
            $this->service->upsert($consulta->id, $dto);
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Signos vitales guardados exitosamente.');
    }

    public function update(StoreVitalSignRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        $dto = VitalSignDTO::fromArray($request->validated());
        try {
            $this->service->upsert($consulta->id, $dto);
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Signos vitales actualizados exitosamente.');
    }

    public function destroy(Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->service->deleteByConsultation($consulta->id);
        } catch (\DomainException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Signos vitales eliminados exitosamente.');
    }
}
