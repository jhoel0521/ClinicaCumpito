<?php

use App\DTOs\Catalogs\OmsCatalogoGraficaDTO;
use App\Models\OmsCatalogoGrafica;
use App\Services\CatalogService;

describe('OmsCatalogoGraficaService', function () {
    beforeEach(function () {
        $this->service = new CatalogService;
    });

    test('createOmsCatalogo() guarda en DB', function () {
        $dto = new OmsCatalogoGraficaDTO(
            id: null,
            nombre: 'Talla para Edad - Niñas',
            codigo: 'WHO_LEN_AGE_F_0_5Y',
            descripcion: 'Gráfica OMS de talla para la edad en niñas de 0 a 5 años.',
            tipo_grafica: 'talla_edad',
            rango_edad: '0-5 años',
            sexo: 'F',
            minimo_z_score: -3,
            maximo_z_score: 3,
            minimo_percentil: 3,
            maximo_percentil: 97,
        );

        $grafica = $this->service->createOmsCatalogo($dto);

        expect($grafica)->toBeInstanceOf(OmsCatalogoGrafica::class);
        expect($grafica->codigo)->toBe('WHO_LEN_AGE_F_0_5Y');
        expect($grafica->sexo)->toBe('F');

        $this->assertDatabaseHas('oms_catalogo_graficas', [
            'codigo' => 'WHO_LEN_AGE_F_0_5Y',
        ]);
    });

    test('updateOmsCatalogo() actualiza los campos', function () {
        $grafica = OmsCatalogoGrafica::factory()->create([
            'nombre' => 'Nombre Original',
            'codigo' => 'WHO_ORIG_001',
            'tipo_grafica' => 'peso_talla',
            'rango_edad' => '0-24 meses',
            'sexo' => 'M',
        ]);

        $dto = new OmsCatalogoGraficaDTO(
            id: $grafica->id,
            nombre: 'Nombre Actualizado',
            codigo: 'WHO_ORIG_001',
            tipo_grafica: 'talla_edad',
            rango_edad: '0-5 años',
            sexo: 'M',
        );

        $updated = $this->service->updateOmsCatalogo($grafica->id, $dto);

        expect($updated->nombre)->toBe('Nombre Actualizado');
        expect($updated->tipo_grafica)->toBe('talla_edad');
    });

    test('deleteOmsCatalogo() hace soft-delete', function () {
        $grafica = OmsCatalogoGrafica::factory()->create([
            'codigo' => 'WHO_DEL_001',
            'tipo_grafica' => 'imc',
            'rango_edad' => '5-19 años',
            'sexo' => 'M',
        ]);

        $result = $this->service->deleteOmsCatalogo($grafica->id);

        expect($result)->toBeTrue();
        $this->assertSoftDeleted('oms_catalogo_graficas', ['id' => $grafica->id]);
    });
});
