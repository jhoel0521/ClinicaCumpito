<?php

namespace App\Contracts;

use App\DTOs\Templates\LaboratoryTemplateDTO;
use App\DTOs\Templates\PrescriptionTemplateDTO;
use App\Models\LaboratoryTemplate;
use App\Models\PrescriptionTemplate;
use Illuminate\Database\Eloquent\Collection;

interface TemplateServiceContract
{
    // Prescription Templates
    public function createPrescriptionTemplate(PrescriptionTemplateDTO $dto): PrescriptionTemplate;

    public function updatePrescriptionTemplate(string $id, PrescriptionTemplateDTO $dto): PrescriptionTemplate;

    public function deletePrescriptionTemplate(string $id): bool;

    /**
     * @return Collection<int, PrescriptionTemplate>
     */
    public function getPrescriptionTemplatesByDoctor(string $doctor_id): Collection;

    // Laboratory Templates
    public function createLaboratoryTemplate(LaboratoryTemplateDTO $dto): LaboratoryTemplate;

    public function updateLaboratoryTemplate(string $id, LaboratoryTemplateDTO $dto): LaboratoryTemplate;

    public function deleteLaboratoryTemplate(string $id): bool;

    /**
     * @return Collection<int, LaboratoryTemplate>
     */
    public function getLaboratoryTemplatesByDoctor(string $doctor_id): Collection;
}
