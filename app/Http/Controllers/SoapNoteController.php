<?php

namespace App\Http\Controllers;

use App\Contracts\SoapNoteServiceContract;
use App\DTOs\SoapNoteDTO;
use App\Http\Requests\StoreSoapNoteRequest;
use App\Models\Consultation;

class SoapNoteController extends Controller
{
    public function __construct(private SoapNoteServiceContract $service) {}

    public function store(StoreSoapNoteRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        $dto = SoapNoteDTO::fromArray($request->validated());
        try {
            $this->service->upsert($consulta->id, $dto);
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Nota SOAP guardada exitosamente.');
    }

    public function update(StoreSoapNoteRequest $request, Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        $dto = SoapNoteDTO::fromArray($request->validated());
        try {
            $this->service->upsert($consulta->id, $dto);
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Nota SOAP actualizada exitosamente.');
    }

    public function destroy(Consultation $consulta): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->service->deleteByConsultation($consulta->id);
        } catch (\DomainException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Nota SOAP eliminada exitosamente.');
    }
}
