<?php

use App\DTOs\Catalogs\OmsDatoGraficaDTO;
use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use App\Services\CatalogService;

describe('OmsDatoGraficaService', function () {
    beforeEach(function () {
        $this->service = new CatalogService;
        // Crear la boleta padre UNA vez para todos los tests del describe
        // (OmsCatalogoGraficaFactory usa unique()->randomElement con 5 variantes)
        $this->catalogo = OmsCatalogoGrafica::factory()->create();
    });

    test('createOmsDato() guarda en DB', function () {
        $dto = new OmsDatoGraficaDTO(
            id: null,
            oms_catalogo_grafica_id: $this->catalogo->id,
            x_value: 6.0,
            l_value: 0.1128,
            m_value: 7.934,
            s_value: 0.1109,
            sd3neg: 5.47,
            sd0: 7.93,
            sd3: 11.00,
            p3: 6.14,
            p50: 7.93,
            p97: 10.37,
        );

        $dato = $this->service->createOmsDato($dto);

        expect($dato)->toBeInstanceOf(OmsDatoGrafica::class);
        expect($dato->oms_catalogo_grafica_id)->toBe($this->catalogo->id);
        expect((float) $dato->x_value)->toBe(6.0);
        expect((float) $dato->m_value)->toBe(7.934);

        $this->assertDatabaseHas('oms_datos_graficas', [
            'oms_catalogo_grafica_id' => $this->catalogo->id,
        ]);
    });

    test('updateOmsDato() actualiza los campos', function () {
        $dato = OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $this->catalogo->id,
            'x_value' => 6.0,
            'l_value' => 0.1128,
            'm_value' => 7.934,
            's_value' => 0.1109,
        ]);

        $dto = new OmsDatoGraficaDTO(
            id: $dato->id,
            oms_catalogo_grafica_id: $this->catalogo->id,
            x_value: 6.0,
            l_value: 0.1128,
            m_value: 8.0,
            s_value: 0.1109,
        );

        $updated = $this->service->updateOmsDato($dato->id, $dto);

        expect((float) $updated->m_value)->toBe(8.0);
        expect($updated->id)->toBe($dato->id);
    });

    test('deleteOmsDato() elimina definitivamente (hard delete)', function () {
        $dato = OmsDatoGrafica::factory()->create([
            'oms_catalogo_grafica_id' => $this->catalogo->id,
            'x_value' => 12.0,
            'l_value' => 0.0139,
            'm_value' => 9.6479,
            's_value' => 0.11243,
        ]);

        $result = $this->service->deleteOmsDato($dato->id);

        expect($result)->toBeTrue();
        expect(OmsDatoGrafica::find($dato->id))->toBeNull();
        $this->assertDatabaseMissing('oms_datos_graficas', ['id' => $dato->id]);
    });
});
