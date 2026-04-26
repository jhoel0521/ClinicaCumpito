<?php

namespace App\Services;

use App\Contracts\LaboratoryRequestServiceContract;
use App\DTOs\LaboratoryRequestDTO;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\ValueObjects\ConsultationStatus;

class LaboratoryRequestService implements LaboratoryRequestServiceContract
{
    public function createForConsultation(string $consultationId, LaboratoryRequestDTO $dto): LaboratoryRequest
    {
        $consultation = Consultation::findOrFail($consultationId);

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede agregar solicitudes a una consulta finalizada.');
        }

        $request = LaboratoryRequest::create([
            'consultation_id' => $consultationId,
            ...$dto->toArray(),
        ]);

        $fresh = $request->fresh(['consultation']);
        if (! $fresh instanceof LaboratoryRequest) {
            throw new \RuntimeException('No se pudo refrescar la solicitud de laboratorio.');
        }

        return $fresh;
    }

    public function update(string $labRequestId, LaboratoryRequestDTO $dto): LaboratoryRequest
    {
        $request = LaboratoryRequest::with('consultation')->findOrFail($labRequestId);
        $consultation = $request->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la solicitud.');
        }

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            // Allow status-only update (e.g., marking received) even after finalization.
            $request->update(['status' => $dto->status]);
            $fresh = $request->fresh(['consultation']);
            if (! $fresh instanceof LaboratoryRequest) {
                throw new \RuntimeException('No se pudo refrescar la solicitud de laboratorio.');
            }

            return $fresh;
        }

        $request->update($dto->toArray());

        $fresh = $request->fresh(['consultation']);
        if (! $fresh instanceof LaboratoryRequest) {
            throw new \RuntimeException('No se pudo refrescar la solicitud de laboratorio.');
        }

        return $fresh;
    }

    public function findByConsultation(string $consultationId): ?LaboratoryRequest
    {
        return LaboratoryRequest::with(['consultation', 'items'])
            ->where('consultation_id', $consultationId)
            ->first();
    }

    public function delete(string $labRequestId): bool
    {
        $request = LaboratoryRequest::with('consultation')->findOrFail($labRequestId);
        $consultation = $request->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la solicitud.');
        }

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede eliminar la solicitud de laboratorio de una consulta finalizada.');
        }

        return (bool) $request->delete();
    }

    private function statusValue(Consultation $consultation): string
    {
        return $consultation->status instanceof ConsultationStatus
            ? $consultation->status->value()
            : (string) $consultation->status;
    }
}
