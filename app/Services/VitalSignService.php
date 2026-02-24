<?php

namespace App\Services;

use App\Contracts\VitalSignServiceContract;
use App\DTOs\VitalSignDTO;
use App\Models\VitalSign;

class VitalSignService implements VitalSignServiceContract
{
    public function upsert(string $consultationId, VitalSignDTO $dto): VitalSign
    {
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
        $vitalSign = VitalSign::where('consultation_id', $consultationId)->first();

        if (! $vitalSign) {
            return false;
        }

        return (bool) $vitalSign->delete();
    }
}
