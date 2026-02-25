<?php

namespace App\Services;

use App\Contracts\LaboratoryRequestItemServiceContract;
use App\DTOs\LaboratoryRequestItemDTO;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\ValueObjects\ConsultationStatus;
use Illuminate\Support\Collection;

class LaboratoryRequestItemService implements LaboratoryRequestItemServiceContract
{
    public function create(string $laboratoryRequestId, LaboratoryRequestItemDTO $dto): LaboratoryRequestItem
    {
        $labRequest = LaboratoryRequest::with('consultation')->findOrFail($laboratoryRequestId);
        $consultation = $labRequest->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la solicitud de laboratorio.');
        }

        $this->ensureConsultationEditable($consultation);

        $item = LaboratoryRequestItem::create([
            'laboratory_request_id' => $laboratoryRequestId,
            ...$dto->toArray(),
        ]);

        $freshItem = $item->fresh(['laboratoryRequest']);
        if (! $freshItem instanceof LaboratoryRequestItem) {
            throw new \RuntimeException('No se pudo refrescar el detalle de solicitud de laboratorio.');
        }

        return $freshItem;
    }

    public function update(string $laboratoryRequestItemId, LaboratoryRequestItemDTO $dto): LaboratoryRequestItem
    {
        $item = LaboratoryRequestItem::with('laboratoryRequest.consultation')->findOrFail($laboratoryRequestItemId);
        $consultation = $item->laboratoryRequest?->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la solicitud de laboratorio.');
        }

        $this->ensureConsultationEditable($consultation);
        $item->update($dto->toArray());

        $freshItem = $item->fresh(['laboratoryRequest']);
        if (! $freshItem instanceof LaboratoryRequestItem) {
            throw new \RuntimeException('No se pudo refrescar el detalle de solicitud de laboratorio.');
        }

        return $freshItem;
    }

    public function listByRequest(string $laboratoryRequestId): Collection
    {
        return LaboratoryRequestItem::query()
            ->where('laboratory_request_id', $laboratoryRequestId)
            ->orderBy('created_at')
            ->get();
    }

    public function delete(string $laboratoryRequestItemId): bool
    {
        $item = LaboratoryRequestItem::with('laboratoryRequest.consultation')->findOrFail($laboratoryRequestItemId);
        $consultation = $item->laboratoryRequest?->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la solicitud de laboratorio.');
        }

        $this->ensureConsultationEditable($consultation);

        return (bool) $item->delete();
    }

    private function ensureConsultationEditable(Consultation $consultation): void
    {
        $status = $consultation->status instanceof ConsultationStatus
            ? $consultation->status->value()
            : (string) $consultation->status;

        if ($status === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede editar el detalle de solicitud de laboratorio en una consulta finalizada.');
        }
    }
}
