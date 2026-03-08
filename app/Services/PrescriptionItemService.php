<?php

namespace App\Services;

use App\Contracts\PrescriptionItemServiceContract;
use App\DTOs\PrescriptionItemDTO;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\ValueObjects\ConsultationStatus;
use Illuminate\Support\Collection;

class PrescriptionItemService implements PrescriptionItemServiceContract
{
    public function create(string $prescriptionId, PrescriptionItemDTO $dto): PrescriptionItem
    {
        $prescription = Prescription::with('consultation')->findOrFail($prescriptionId);
        $consultation = $prescription->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la receta.');
        }

        $this->ensureConsultationEditable($consultation);

        $item = PrescriptionItem::create([
            'prescription_id' => $prescriptionId,
            ...$dto->toArray(),
        ]);

        $freshItem = $item->fresh(['prescription']);
        if (! $freshItem instanceof PrescriptionItem) {
            throw new \RuntimeException('No se pudo refrescar el detalle de receta.');
        }

        return $freshItem;
    }

    public function update(string $prescriptionItemId, PrescriptionItemDTO $dto): PrescriptionItem
    {
        $item = PrescriptionItem::with('prescription.consultation')->findOrFail($prescriptionItemId);
        $consultation = $item->prescription?->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la receta.');
        }

        $this->ensureConsultationEditable($consultation);
        $item->update($dto->toArray());

        $freshItem = $item->fresh(['prescription']);
        if (! $freshItem instanceof PrescriptionItem) {
            throw new \RuntimeException('No se pudo refrescar el detalle de receta.');
        }

        return $freshItem;
    }

    public function listByPrescription(string $prescriptionId): Collection
    {
        return PrescriptionItem::query()
            ->where('prescription_id', $prescriptionId)
            ->orderBy('created_at')
            ->get();
    }

    public function delete(string $prescriptionItemId): bool
    {
        $item = PrescriptionItem::with('prescription.consultation')->findOrFail($prescriptionItemId);
        $consultation = $item->prescription?->consultation;

        if (! $consultation instanceof Consultation) {
            throw new \RuntimeException('No se encontró la consulta asociada a la receta.');
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
            throw new \DomainException('No se puede editar el detalle de receta en una consulta finalizada.');
        }
    }
}
