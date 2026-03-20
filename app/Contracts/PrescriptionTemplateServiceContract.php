<?php

namespace App\Contracts;

use App\DTOs\Templates\PrescriptionTemplateDTO;
use App\Models\PrescriptionTemplate;
use Illuminate\Database\Eloquent\Collection;

interface PrescriptionTemplateServiceContract
{
    public function createPrescriptionTemplate(PrescriptionTemplateDTO $dto): PrescriptionTemplate;

    public function updatePrescriptionTemplate(string $id, PrescriptionTemplateDTO $dto): PrescriptionTemplate;

    public function deletePrescriptionTemplate(string $id): bool;

    /**
     * @return Collection<int, PrescriptionTemplate>
     */
    public function getPrescriptionTemplatesByDoctor(string $doctor_id): Collection;
}
