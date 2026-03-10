<?php

namespace App\Services;

use App\Contracts\PrescriptionServiceContract;
use App\DTOs\PrescriptionDTO;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionAppliedTemplate;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionTemplate;
use App\ValueObjects\ConsultationStatus;

class PrescriptionService implements PrescriptionServiceContract
{
    public function upsert(string $consultationId, PrescriptionDTO $dto): Prescription
    {
        $consultation = Consultation::findOrFail($consultationId);

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede editar la receta de una consulta finalizada.');
        }

        $prescription = Prescription::updateOrCreate(
            ['consultation_id' => $consultationId],
            $dto->toArray()
        );

        $freshPrescription = $prescription->fresh(['consultation']);
        if (! $freshPrescription instanceof Prescription) {
            throw new \RuntimeException('No se pudo refrescar la receta.');
        }

        return $freshPrescription;
    }

    public function applyTemplate(string $prescriptionId, string $templateId): Prescription
    {
        $prescription = Prescription::with('consultation')->findOrFail($prescriptionId);
        $consultation = $prescription->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la receta.');
        }

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede editar la receta de una consulta finalizada.');
        }

        $template = PrescriptionTemplate::with('items.medication')->findOrFail($templateId);

        // Snapshot: copy each template item to prescription items
        foreach ($template->items as $templateItem) {
            $medicationName = $templateItem->custom_medication_name !== null
                ? $templateItem->custom_medication_name
                : ($templateItem->medication !== null ? $templateItem->medication->name : '');

            PrescriptionItem::create([
                'prescription_id' => $prescriptionId,
                'medication_name' => $medicationName,
                'dose' => $templateItem->dose ?? '',
                'frequency' => $templateItem->frequency ?? '',
                'duration' => $templateItem->duration ?? '',
                'instructions' => $templateItem->instructions,
            ]);
        }

        // Record which template was applied (audit trail)
        PrescriptionAppliedTemplate::create([
            'prescription_id' => $prescriptionId,
            'template_id' => $templateId,
            'template_name' => $template->name,
            'applied_at' => now(),
        ]);

        return $prescription->fresh(['consultation', 'items', 'appliedTemplates'])
            ?? $prescription;
    }

    public function findByConsultation(string $consultationId): ?Prescription
    {
        return Prescription::with(['consultation', 'items', 'appliedTemplates'])
            ->where('consultation_id', $consultationId)
            ->first();
    }

    public function deleteByConsultation(string $consultationId): bool
    {
        $consultation = Consultation::findOrFail($consultationId);

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede eliminar la receta de una consulta finalizada.');
        }

        $prescription = Prescription::where('consultation_id', $consultationId)->first();

        if (! $prescription) {
            return false;
        }

        return (bool) $prescription->delete();
    }

    private function statusValue(Consultation $consultation): string
    {
        return $consultation->status instanceof ConsultationStatus
            ? $consultation->status->value()
            : (string) $consultation->status;
    }
}
