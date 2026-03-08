<?php

namespace App\Contracts;

use App\DTOs\SoapNoteDTO;
use App\Models\SoapNote;

interface SoapNoteServiceContract
{
    public function upsert(string $consultationId, SoapNoteDTO $dto): SoapNote;

    public function findByConsultation(string $consultationId): ?SoapNote;

    public function deleteByConsultation(string $consultationId): bool;
}
