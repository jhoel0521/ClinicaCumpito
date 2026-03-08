<?php

namespace App\Contracts;

use App\DTOs\PrescriptionItemDTO;
use App\Models\PrescriptionItem;
use Illuminate\Support\Collection;

interface PrescriptionItemServiceContract
{
    public function create(string $prescriptionId, PrescriptionItemDTO $dto): PrescriptionItem;

    public function update(string $prescriptionItemId, PrescriptionItemDTO $dto): PrescriptionItem;

    /** @return Collection<int, PrescriptionItem> */
    public function listByPrescription(string $prescriptionId): Collection;

    public function delete(string $prescriptionItemId): bool;
}
