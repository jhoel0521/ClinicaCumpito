<?php

namespace App\Contracts;

use App\DTOs\PrescriptionDTO;
use App\Models\Prescription;
use Illuminate\Support\Collection;

interface PrescriptionServiceContract
{
    public function createForConsultation(string $consultationId, PrescriptionDTO $dto): Prescription;

    public function update(string $prescriptionId, PrescriptionDTO $dto): Prescription;

    public function applyTemplate(string $prescriptionId, string $templateId): Prescription;

    /** @return Collection<int, Prescription> */
    public function listByConsultation(string $consultationId): Collection;

    public function delete(string $prescriptionId): bool;
}
