<?php

namespace App\Contracts;

use App\DTOs\Catalogs\LaboratoryCategoryDTO;
use App\DTOs\Catalogs\LaboratoryExamDTO;
use App\DTOs\Catalogs\MedicalConditionDTO;
use App\DTOs\Catalogs\OmsCatalogoGraficaDTO;
use App\DTOs\Catalogs\OmsDatoGraficaDTO;
use App\DTOs\Catalogs\VaccineDTO;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\MedicalCondition;
use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
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

    // Vaccines
    public function createVaccine(VaccineDTO $dto): Vaccine;

    public function updateVaccine(string $id, VaccineDTO $dto): Vaccine;

    public function deleteVaccine(string $id): bool;

    /**
     * @return Collection<int, Vaccine>
     */
    public function getAllVaccines(): Collection;

    // OMS Catalogo Graficas
    public function createOmsCatalogo(OmsCatalogoGraficaDTO $dto): OmsCatalogoGrafica;

    public function updateOmsCatalogo(string $id, OmsCatalogoGraficaDTO $dto): OmsCatalogoGrafica;

    public function deleteOmsCatalogo(string $id): bool;

    /**
     * @return Collection<int, OmsCatalogoGrafica>
     */
    public function getAllOmsCatalogos(): Collection;

    // OMS Datos Graficas
    public function createOmsDato(OmsDatoGraficaDTO $dto): OmsDatoGrafica;

    public function updateOmsDato(string $id, OmsDatoGraficaDTO $dto): OmsDatoGrafica;

    public function deleteOmsDato(string $id): bool;

    /**
     * @return Collection<int, OmsDatoGrafica>
     */
    public function getDatosByGrafica(string $graficaId): Collection;

    // Medical Conditions
    public function createMedicalCondition(MedicalConditionDTO $dto): MedicalCondition;

    public function updateMedicalCondition(string $id, MedicalConditionDTO $dto): MedicalCondition;

    public function deleteMedicalCondition(string $id): bool;

    /**
     * @return Collection<int, MedicalCondition>
     */
    public function getAllMedicalConditions(): Collection;
}
