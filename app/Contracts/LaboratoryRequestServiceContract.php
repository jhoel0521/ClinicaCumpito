<?php

namespace App\Contracts;

use App\DTOs\LaboratoryRequestDTO;
use App\Models\LaboratoryRequest;

interface LaboratoryRequestServiceContract
{
    public function createForConsultation(string $consultationId, LaboratoryRequestDTO $dto): LaboratoryRequest;

    public function update(string $labRequestId, LaboratoryRequestDTO $dto): LaboratoryRequest;

    public function applyTemplate(string $laboratoryRequestId, string $templateId): LaboratoryRequest;

    public function findByConsultation(string $consultationId): ?LaboratoryRequest;

    public function delete(string $labRequestId): bool;
}
