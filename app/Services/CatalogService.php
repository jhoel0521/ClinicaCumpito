<?php

namespace App\Services;

use App\Contracts\CatalogServiceContract;
use App\DTOs\Catalogs\LaboratoryCategoryDTO;
use App\DTOs\Catalogs\LaboratoryExamDTO;
use App\DTOs\Catalogs\MedicationDTO;
use App\DTOs\Catalogs\VaccineDTO;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\Medication;
use App\Models\Vaccine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CatalogService implements CatalogServiceContract
{
    // Laboratory Categories
    public function createLaboratoryCategory(LaboratoryCategoryDTO $dto): LaboratoryCategory
    {
        return DB::transaction(fn () => LaboratoryCategory::create($dto->toArray()));
    }

    public function updateLaboratoryCategory(string $id, LaboratoryCategoryDTO $dto): LaboratoryCategory
    {
        return DB::transaction(function () use ($id, $dto) {
            $category = LaboratoryCategory::findOrFail($id);
            $category->update($dto->toArray());

            return $category;
        });
    }

    public function deleteLaboratoryCategory(string $id): bool
    {
        return DB::transaction(fn () => LaboratoryCategory::findOrFail($id)->delete());
    }

    public function getAllLaboratoryCategories(): Collection
    {
        return LaboratoryCategory::all();
    }

    // Laboratory Exams
    public function createLaboratoryExam(LaboratoryExamDTO $dto): LaboratoryExam
    {
        return DB::transaction(fn () => LaboratoryExam::create($dto->toArray()));
    }

    public function updateLaboratoryExam(string $id, LaboratoryExamDTO $dto): LaboratoryExam
    {
        return DB::transaction(function () use ($id, $dto) {
            $exam = LaboratoryExam::findOrFail($id);
            $exam->update($dto->toArray());

            return $exam;
        });
    }

    public function deleteLaboratoryExam(string $id): bool
    {
        return DB::transaction(fn () => LaboratoryExam::findOrFail($id)->delete());
    }

    public function getExamsByCategory(string $categoryId): Collection
    {
        return LaboratoryExam::where('category_id', $categoryId)->get();
    }

    // Medications
    public function createMedication(MedicationDTO $dto): Medication
    {
        return DB::transaction(fn () => Medication::create($dto->toArray()));
    }

    public function updateMedication(string $id, MedicationDTO $dto): Medication
    {
        return DB::transaction(function () use ($id, $dto) {
            $medication = Medication::findOrFail($id);
            $medication->update($dto->toArray());

            return $medication;
        });
    }

    public function deleteMedication(string $id): bool
    {
        return DB::transaction(fn () => Medication::findOrFail($id)->delete());
    }

    public function getAllMedications(): Collection
    {
        return Medication::all();
    }

    // Vaccines
    public function createVaccine(VaccineDTO $dto): Vaccine
    {
        return DB::transaction(fn () => Vaccine::create($dto->toArray()));
    }

    public function updateVaccine(string $id, VaccineDTO $dto): Vaccine
    {
        return DB::transaction(function () use ($id, $dto) {
            $vaccine = Vaccine::findOrFail($id);
            $vaccine->update($dto->toArray());

            return $vaccine;
        });
    }

    public function deleteVaccine(string $id): bool
    {
        return DB::transaction(fn () => Vaccine::findOrFail($id)->delete());
    }

    public function getAllVaccines(): Collection
    {
        return Vaccine::all();
    }
}
