<?php

namespace App\Services;

use App\Contracts\LaboratoryRequestServiceContract;
use App\DTOs\LaboratoryRequestDTO;
use App\Models\Consultation;
use App\Models\LaboratoryAppliedTemplate;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryTemplate;
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

        $freshRequest = $request->fresh(['consultation']);
        if (! $freshRequest instanceof LaboratoryRequest) {
            throw new \RuntimeException('No se pudo refrescar la solicitud de laboratorio.');
        }

        return $freshRequest;
    }

    public function applyTemplate(string $laboratoryRequestId, string $templateId): LaboratoryRequest
    {
        $labRequest = LaboratoryRequest::with('consultation')->findOrFail($laboratoryRequestId);
        $consultation = $labRequest->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la solicitud de laboratorio.');
        }

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede editar la solicitud de laboratorio de una consulta finalizada.');
        }

        $template = LaboratoryTemplate::with('items.exam')->findOrFail($templateId);

        // Snapshot: copy each template item to request items
        foreach ($template->items as $templateItem) {
            $examName = $templateItem->custom_exam_name !== null
                ? $templateItem->custom_exam_name
                : ($templateItem->exam !== null ? $templateItem->exam->name : '');

            LaboratoryRequestItem::create([
                'laboratory_request_id' => $laboratoryRequestId,
                'exam_name' => $examName,
                'indications' => $templateItem->indications,
            ]);
        }

        // Record which template was applied
        LaboratoryAppliedTemplate::create([
            'laboratory_request_id' => $laboratoryRequestId,
            'template_id' => $templateId,
            'template_name' => $template->name,
            'applied_at' => now(),
        ]);

        return $labRequest->fresh(['consultation', 'items', 'appliedTemplates'])
            ?? $labRequest;
    }

    public function findByConsultation(string $consultationId): ?LaboratoryRequest
    {
        return LaboratoryRequest::with(['consultation', 'items', 'appliedTemplates'])
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
