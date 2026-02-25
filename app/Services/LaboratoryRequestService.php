<?php

namespace App\Services;

use App\Contracts\LaboratoryRequestServiceContract;
use App\DTOs\LaboratoryRequestDTO;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\ValueObjects\ConsultationStatus;

class LaboratoryRequestService implements LaboratoryRequestServiceContract
{
    public function upsert(string $consultationId, LaboratoryRequestDTO $dto): LaboratoryRequest
    {
        $consultation = Consultation::findOrFail($consultationId);

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede editar la solicitud de laboratorio de una consulta finalizada.');
        }

        $request = LaboratoryRequest::updateOrCreate(
            ['consultation_id' => $consultationId],
            $dto->toArray()
        );

        $freshRequest = $request->fresh(['consultation', 'sourceTemplate']);
        if (! $freshRequest instanceof LaboratoryRequest) {
            throw new \RuntimeException('No se pudo refrescar la solicitud de laboratorio.');
        }

        return $freshRequest;
    }

    public function findByConsultation(string $consultationId): ?LaboratoryRequest
    {
        return LaboratoryRequest::with(['consultation', 'sourceTemplate'])
            ->where('consultation_id', $consultationId)
            ->first();
    }

    public function deleteByConsultation(string $consultationId): bool
    {
        $consultation = Consultation::findOrFail($consultationId);

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede eliminar la solicitud de laboratorio de una consulta finalizada.');
        }

        $request = LaboratoryRequest::where('consultation_id', $consultationId)->first();

        if (! $request) {
            return false;
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
