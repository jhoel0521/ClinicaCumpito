<?php

use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use Database\Seeders\LaboratoryCatalogSeeder;

describe('LaboratoryCatalogSeeder', function () {

    beforeEach(function () {
        $this->seed(LaboratoryCatalogSeeder::class);
    });

    it('el análisis de heces (Coprológico) incluye la opción Moco', function () {
        $coprologico = LaboratoryExam::where('name', 'Coprológico (COPR)')->firstOrFail();

        expect($coprologico->parameters->pluck('name'))->toContain('Moco');
    });

    it('es idempotente y no duplica parámetros al re-ejecutarse', function () {
        $this->seed(LaboratoryCatalogSeeder::class);

        $coprologico = LaboratoryExam::where('name', 'Coprológico (COPR)')->firstOrFail();

        expect($coprologico->parameters()->where('name', 'Moco')->count())->toBe(1);
    });

    it('sigue cargando el catálogo completo de categorías', function () {
        expect(LaboratoryCategory::count())->toBeGreaterThanOrEqual(7);
    });
});
