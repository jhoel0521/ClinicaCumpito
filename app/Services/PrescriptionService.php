<?php

namespace App\Services;

use App\Contracts\PrescriptionServiceContract;
use App\DTOs\PrescriptionDTO;
use App\Models\Consultation;
use App\Models\Prescription;
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

        $freshPrescription = $prescription->fresh(['consultation', 'sourceTemplate']);
        if (! $freshPrescription instanceof Prescription) {
            throw new \RuntimeException('No se pudo refrescar la receta.');
        }

        return $freshPrescription;
    }

    public function findByConsultation(string $consultationId): ?Prescription
    {
        return Prescription::with(['consultation', 'sourceTemplate'])
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
