<?php

namespace App\Contracts;

use App\DTOs\PrescriptionDTO;
use App\Models\Prescription;

interface PrescriptionServiceContract
{
    public function upsert(string $consultationId, PrescriptionDTO $dto): Prescription;

    public function applyTemplate(string $prescriptionId, string $templateId): Prescription;

    public function findByConsultation(string $consultationId): ?Prescription;

    public function deleteByConsultation(string $consultationId): bool;
}
