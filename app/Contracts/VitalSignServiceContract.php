<?php

namespace App\Contracts;

use App\DTOs\VitalSignDTO;
use App\Models\VitalSign;

interface VitalSignServiceContract
{
    public function upsert(string $consultationId, VitalSignDTO $dto): VitalSign;

    public function findByConsultation(string $consultationId): ?VitalSign;

    public function deleteByConsultation(string $consultationId): bool;
}
