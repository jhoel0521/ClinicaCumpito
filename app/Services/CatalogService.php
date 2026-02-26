<?php

namespace App\Services;

use App\Contracts\CatalogServiceContract;
use App\DTOs\Catalogs\LaboratoryCategoryDTO;
use App\DTOs\Catalogs\LaboratoryExamDTO;
use App\DTOs\Catalogs\MedicationDTO;
use App\DTOs\Catalogs\OmsCatalogoGraficaDTO;
use App\DTOs\Catalogs\VaccineDTO;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\Medication;
use App\Models\OmsCatalogoGrafica;
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
        return (bool) DB::transaction(fn () => LaboratoryCategory::findOrFail($id)->delete());
    }

    /**
     * @return Collection<int, LaboratoryCategory>
     */
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
        return (bool) DB::transaction(fn () => LaboratoryExam::findOrFail($id)->delete());
    }

    /**
     * @return Collection<int, LaboratoryExam>
     */
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
        return (bool) DB::transaction(fn () => Medication::findOrFail($id)->delete());
    }

    /**
     * @return Collection<int, Medication>
     */
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
        return (bool) DB::transaction(fn () => Vaccine::findOrFail($id)->delete());
    }

    /**
     * @return Collection<int, Vaccine>
     */
    public function getAllVaccines(): Collection
    {
        return Vaccine::all();
    }

    // OMS Catalogo Graficas
    public function createOmsCatalogo(OmsCatalogoGraficaDTO $dto): OmsCatalogoGrafica
    {
        return DB::transaction(fn () => OmsCatalogoGrafica::create($dto->toArray()));
    }

    public function updateOmsCatalogo(string $id, OmsCatalogoGraficaDTO $dto): OmsCatalogoGrafica
    {
        return DB::transaction(function () use ($id, $dto) {
            $grafica = OmsCatalogoGrafica::findOrFail($id);
            $grafica->update($dto->toArray());

            return $grafica;
        });
    }

    public function deleteOmsCatalogo(string $id): bool
    {
        return (bool) DB::transaction(fn () => OmsCatalogoGrafica::findOrFail($id)->delete());
    }

    /**
     * @return Collection<int, OmsCatalogoGrafica>
     */
    public function getAllOmsCatalogos(): Collection
    {
        return OmsCatalogoGrafica::all();
    }
}
