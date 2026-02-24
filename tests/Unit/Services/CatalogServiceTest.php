<?php

use App\DTOs\Catalogs\LaboratoryCategoryDTO;
use App\DTOs\Catalogs\LaboratoryExamDTO;
use App\DTOs\Catalogs\MedicationDTO;
use App\DTOs\Catalogs\VaccineDTO;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\Medication;
use App\Models\Vaccine;
use App\Services\CatalogService;

describe('CatalogService', function () {
    beforeEach(function () {
        $this->service = new CatalogService;
    });

    test('puede crear y actualizar categoria de laboratorio', function () {
        $createDto = new LaboratoryCategoryDTO(
            id: null,
            name: 'Microbiología Clínica',
            description: 'Categoría para estudios microbiológicos',
        );

        $category = $this->service->createLaboratoryCategory($createDto);

        expect($category)->toBeInstanceOf(LaboratoryCategory::class);
        expect($category->name)->toBe('Microbiología Clínica');

        $updateDto = new LaboratoryCategoryDTO(
            id: $category->id,
            name: 'Microbiología Actualizada',
            description: 'Descripción actualizada',
        );

        $updated = $this->service->updateLaboratoryCategory($category->id, $updateDto);

        expect($updated->name)->toBe('Microbiología Actualizada');
    })->group('catalog-service');

    test('puede crear examen y listarlo por categoria', function () {
        $category = LaboratoryCategory::factory()->create();

        $dto = new LaboratoryExamDTO(
            id: null,
            category_id: $category->id,
            name: 'Hemoglobina',
            description: 'Nivel de hemoglobina',
            unit: 'g/dL',
            reference_range: '11 - 15',
        );

        $exam = $this->service->createLaboratoryExam($dto);
        $exams = $this->service->getExamsByCategory($category->id);

        expect($exam)->toBeInstanceOf(LaboratoryExam::class);
        expect($exams->pluck('id'))->toContain($exam->id);
    })->group('catalog-service');

    test('puede actualizar y eliminar examen de laboratorio', function () {
        $exam = LaboratoryExam::factory()->create();

        $dto = new LaboratoryExamDTO(
            id: $exam->id,
            category_id: $exam->category_id,
            name: 'Examen Actualizado',
            description: 'Nueva descripción',
            unit: 'mg/dL',
            reference_range: '0 - 100',
        );

        $updated = $this->service->updateLaboratoryExam($exam->id, $dto);
        $deleted = $this->service->deleteLaboratoryExam($exam->id);

        expect($updated->name)->toBe('Examen Actualizado');
        expect($deleted)->toBeTrue();
        expect(LaboratoryExam::find($exam->id))->toBeNull();
    })->group('catalog-service');

    test('puede crear, actualizar y eliminar medicamento', function () {
        $createDto = new MedicationDTO(
            id: null,
            name: 'Paracetamol',
            generic_name: 'Acetaminofén',
            pharmaceutical_form: 'Jarabe',
            concentration: '120mg/5ml',
        );

        $medication = $this->service->createMedication($createDto);

        $updateDto = new MedicationDTO(
            id: $medication->id,
            name: 'Paracetamol Forte',
            generic_name: 'Acetaminofén',
            pharmaceutical_form: 'Tableta',
            concentration: '500mg',
        );

        $updated = $this->service->updateMedication($medication->id, $updateDto);
        $deleted = $this->service->deleteMedication($medication->id);

        expect($updated->name)->toBe('Paracetamol Forte');
        expect($deleted)->toBeTrue();
        expect(Medication::find($medication->id))->toBeNull();
    })->group('catalog-service');

    test('puede crear, actualizar y eliminar vacuna', function () {
        $createDto = new VaccineDTO(
            id: null,
            name: 'BCG Refuerzo',
            disease_prevented: 'Tuberculosis',
            recommended_age: '4 años',
            dose_sequence: 2,
        );

        $vaccine = $this->service->createVaccine($createDto);

        $updateDto = new VaccineDTO(
            id: $vaccine->id,
            name: 'BCG Refuerzo Ajustada',
            disease_prevented: 'Tuberculosis',
            recommended_age: '5 años',
            dose_sequence: 3,
        );

        $updated = $this->service->updateVaccine($vaccine->id, $updateDto);
        $deleted = $this->service->deleteVaccine($vaccine->id);

        expect($updated->name)->toBe('BCG Refuerzo Ajustada');
        expect($deleted)->toBeTrue();
        expect(Vaccine::find($vaccine->id))->toBeNull();
    })->group('catalog-service');

    test('puede listar catalogos activos de medicaciones, vacunas y categorias', function () {
        LaboratoryCategory::factory()->count(2)->create();
        Medication::factory()->count(3)->create();
        Vaccine::factory()->count(2)->create();

        $categories = $this->service->getAllLaboratoryCategories();
        $medications = $this->service->getAllMedications();
        $vaccines = $this->service->getAllVaccines();

        expect($categories->count())->toBeGreaterThanOrEqual(2);
        expect($medications->count())->toBeGreaterThanOrEqual(3);
        expect($vaccines->count())->toBeGreaterThanOrEqual(2);
    })->group('catalog-service');
});
