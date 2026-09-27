<?php

namespace App\Services;

use App\Contracts\VitalSignServiceContract;
use App\DTOs\VitalSignDTO;
use App\Models\Consultation;
use App\Models\VitalSign;
use App\ValueObjects\ConsultationStatus;

class VitalSignService implements VitalSignServiceContract
{
    public function upsert(string $consultationId, VitalSignDTO $dto): VitalSign
    {
        $this->ensureNotFinalized($consultationId);

        $vitalSign = VitalSign::updateOrCreate(
            ['consultation_id' => $consultationId],
            $dto->toArray()
        );

        $freshVitalSign = $vitalSign->fresh();
        if (! $freshVitalSign instanceof VitalSign) {
            throw new \RuntimeException('No se pudo refrescar los signos vitales.');
        }

        return $freshVitalSign;
    }

    public function findByConsultation(string $consultationId): ?VitalSign
    {
        return VitalSign::where('consultation_id', $consultationId)->first();
    }

    public function deleteByConsultation(string $consultationId): bool
    {
        $this->ensureNotFinalized($consultationId);

        $vitalSign = VitalSign::where('consultation_id', $consultationId)->first();

        if (! $vitalSign) {
            return false;
        }

        return (bool) $vitalSign->delete();
    }

    /**
     * Inmutabilidad clínica: se valida contra la BD, no contra el estado del
     * componente (pestañas viejas o guardados que llegan tarde por red lenta).
     */
    private function ensureNotFinalized(string $consultationId): void
    {
        $consultation = Consultation::findOrFail($consultationId);

        if ($consultation->status instanceof ConsultationStatus && $consultation->status->isFinalized()) {
            throw new \DomainException('La consulta está finalizada: los signos vitales ya no se pueden modificar.');
        }
    }
}
