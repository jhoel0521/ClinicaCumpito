<?php

namespace App\Contracts;

use App\DTOs\ConsultationDTO;
use App\Models\Consultation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConsultationServiceContract
{
    public function create(ConsultationDTO $dto): Consultation;

    public function update(string $id, ConsultationDTO $dto): Consultation;

    public function delete(string $id): bool;

    public function discardDraft(string $id): bool;

    public function find(string $id): ?Consultation;

    /**
     * @return LengthAwarePaginator<int, Consultation>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
