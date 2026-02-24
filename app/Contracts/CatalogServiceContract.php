<?php

namespace App\Contracts;

use App\DTOs\Catalogs\LaboratoryCategoryDTO;
use App\DTOs\Catalogs\LaboratoryExamDTO;
use App\DTOs\Catalogs\MedicationDTO;
use App\DTOs\Catalogs\VaccineDTO;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\Medication;
use App\Models\Vaccine;
use Illuminate\Database\Eloquent\Collection;

interface CatalogServiceContract
{
    // Laboratory Categories
    public function createLaboratoryCategory(LaboratoryCategoryDTO $dto): LaboratoryCategory;

    public function updateLaboratoryCategory(string $id, LaboratoryCategoryDTO $dto): LaboratoryCategory;

    public function deleteLaboratoryCategory(string $id): bool;

    /**
     * @return Collection<int, LaboratoryCategory>
     */
    public function getAllLaboratoryCategories(): Collection;

    // Laboratory Exams
    public function createLaboratoryExam(LaboratoryExamDTO $dto): LaboratoryExam;

    public function updateLaboratoryExam(string $id, LaboratoryExamDTO $dto): LaboratoryExam;

    public function deleteLaboratoryExam(string $id): bool;

    /**
     * @return Collection<int, LaboratoryExam>
     */
    public function getExamsByCategory(string $categoryId): Collection;

    // Medications
    public function createMedication(MedicationDTO $dto): Medication;

    public function updateMedication(string $id, MedicationDTO $dto): Medication;

    public function deleteMedication(string $id): bool;

    /**
     * @return Collection<int, Medication>
     */
    public function getAllMedications(): Collection;

    // Vaccines
    public function createVaccine(VaccineDTO $dto): Vaccine;

    public function updateVaccine(string $id, VaccineDTO $dto): Vaccine;

    public function deleteVaccine(string $id): bool;

    /**
     * @return Collection<int, Vaccine>
     */
    public function getAllVaccines(): Collection;
}
