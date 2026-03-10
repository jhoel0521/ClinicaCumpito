<?php

namespace App\Contracts;

use App\DTOs\LaboratoryRequestItemDTO;
use App\Models\LaboratoryRequestItem;
use Illuminate\Support\Collection;

interface LaboratoryRequestItemServiceContract
{
    public function create(string $laboratoryRequestId, LaboratoryRequestItemDTO $dto): LaboratoryRequestItem;

    public function update(string $laboratoryRequestItemId, LaboratoryRequestItemDTO $dto): LaboratoryRequestItem;

    public function updateResult(string $laboratoryRequestItemId, ?string $resultValue, bool $isAbnormal, ?string $resultNotes): LaboratoryRequestItem;

    /** @return Collection<int, LaboratoryRequestItem> */
    public function listByRequest(string $laboratoryRequestId): Collection;

    public function delete(string $laboratoryRequestItemId): bool;
}
