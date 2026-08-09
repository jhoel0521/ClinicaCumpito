<?php

namespace App\Services;

use App\Contracts\PrescriptionServiceContract;
use App\DTOs\PrescriptionDTO;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionTemplate;
use App\ValueObjects\ConsultationStatus;
use Illuminate\Support\Collection;

class PrescriptionService implements PrescriptionServiceContract
{
    public function createForConsultation(string $consultationId, PrescriptionDTO $dto): Prescription
    {
        $consultation = Consultation::findOrFail($consultationId);

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede agregar recetas a una consulta finalizada.');
        }

        $prescription = Prescription::create([
            'consultation_id' => $consultationId,
            ...$dto->toArray(),
        ]);

        $fresh = $prescription->fresh(['consultation']);
        if (! $fresh instanceof Prescription) {
            throw new \RuntimeException('No se pudo refrescar la receta.');
        }

        return $fresh;
    }

    public function update(string $prescriptionId, PrescriptionDTO $dto): Prescription
    {
        $prescription = Prescription::with('consultation')->findOrFail($prescriptionId);
        $consultation = $prescription->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la receta.');
        }

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede editar la receta de una consulta finalizada.');
        }

        $prescription->update($dto->toArray());

        $fresh = $prescription->fresh(['consultation']);
        if (! $fresh instanceof Prescription) {
            throw new \RuntimeException('No se pudo refrescar la receta.');
        }

        return $fresh;
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

        $template = PrescriptionTemplate::with('items')->findOrFail($templateId);

        // Snapshot: copy each template item to prescription items
        foreach ($template->items as $templateItem) {
            $medicationName = $templateItem->custom_medication_name ?? '';

            PrescriptionItem::create([
                'prescription_id' => $prescriptionId,
                'medication_name' => $medicationName,
                'dose' => $templateItem->dose ?? '',
                'frequency' => $templateItem->frequency ?? '',
                'duration' => $templateItem->duration ?? '',
                'instructions' => $templateItem->instructions,
            ]);
        }

        return $prescription->fresh(['consultation', 'items'])
            ?? $prescription;
    }

    /** @return Collection<int, Prescription> */
    public function listByConsultation(string $consultationId): Collection
    {
        return Prescription::with(['items'])
            ->where('consultation_id', $consultationId)
            ->orderBy('created_at')
            ->get();
    }

    public function delete(string $prescriptionId): bool
    {
        $prescription = Prescription::with('consultation')->findOrFail($prescriptionId);
        $consultation = $prescription->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la receta.');
        }

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede eliminar la receta de una consulta finalizada.');
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
