<?php

use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use Database\Seeders\WhoDataSeeder;

describe('WhoDataSeeder', function () {

    beforeEach(function () {
        $this->seed(WhoDataSeeder::class);
    });

    it('crea exactamente 10 OmsCatalogoGrafica', function () {
        expect(OmsCatalogoGrafica::count())->toBe(10);
    });

    it('crea 2 boletas por cada tipo de gráfica (M + F)', function () {
        $tipos = ['peso_edad', 'talla_edad', 'perimetro_cefalico', 'imc', 'peso_talla'];
        foreach ($tipos as $tipo) {
            expect(OmsCatalogoGrafica::where('tipo_grafica', $tipo)->count())
                ->toBe(2, "Se esperaban 2 boletas para tipo_grafica={$tipo}");
        }
    });

    it('cada boleta tiene al menos un dato OmsDatoGrafica', function () {
        OmsCatalogoGrafica::all()->each(function (OmsCatalogoGrafica $g) {
            expect($g->datos()->count())
                ->toBeGreaterThan(0, "La boleta {$g->codigo} no tiene datos");
        });
    });

    it('todos los datos tienen valores LMS no nulos', function () {
        OmsDatoGrafica::select(['l_value', 'm_value', 's_value'])
            ->where(fn ($q) => $q->whereNull('l_value')->orWhereNull('m_value')->orWhereNull('s_value'))
            ->get()
            ->each(fn ($d) => expect(true)->toBeFalse('Dato con LMS nulo encontrado'));

        expect(OmsDatoGrafica::whereNull('l_value')->count())->toBe(0)
            ->and(OmsDatoGrafica::whereNull('m_value')->count())->toBe(0)
            ->and(OmsDatoGrafica::whereNull('s_value')->count())->toBe(0);
    });

    it('peso_edad cubre meses 0 a 60', function () {
        $g = OmsCatalogoGrafica::where('tipo_grafica', 'peso_edad')
            ->where('sexo', 'M')
            ->first();

        expect((float) $g->datos()->min('x_value'))->toBe(0.0)
            ->and((float) $g->datos()->max('x_value'))->toBe(60.0);
    });

    it('talla_edad tiene datos continuos hasta mes 60 (merge 0-2 + 2-5)', function () {
        foreach (['M', 'F'] as $sexo) {
            $g = OmsCatalogoGrafica::where('tipo_grafica', 'talla_edad')
                ->where('sexo', $sexo)
                ->first();

            expect((float) $g->datos()->max('x_value'))
                ->toBe(60.0, "talla_edad_{$sexo} no llega a mes 60");

            // No debe haber saltos (todos los meses 0-60 presentes)
            expect($g->datos()->count())->toBe(61);
        }
    });

    it('imc tiene datos continuos hasta mes 60 (merge 0-2 + 2-5)', function () {
        $g = OmsCatalogoGrafica::where('tipo_grafica', 'imc')
            ->where('sexo', 'M')
            ->first();

        expect((float) $g->datos()->max('x_value'))->toBe(60.0)
            ->and($g->datos()->count())->toBe(61);
    });

    it('peso_talla usa cm como x_value (rango 45–120 cm)', function () {
        foreach (['M', 'F'] as $sexo) {
            $g = OmsCatalogoGrafica::where('tipo_grafica', 'peso_talla')
                ->where('sexo', $sexo)
                ->first();

            expect((float) $g->datos()->min('x_value'))
                ->toBeGreaterThanOrEqual(45.0, "peso_talla_{$sexo} empieza antes de 45 cm")
                ->and((float) $g->datos()->max('x_value'))
                ->toBeGreaterThanOrEqual(119.0, "peso_talla_{$sexo} no llega cerca de 120 cm");
        }
    });

    it('peso_talla no tiene x_values duplicados (merge sin colisiones)', function () {
        OmsCatalogoGrafica::where('tipo_grafica', 'peso_talla')->get()->each(function ($g) {
            $total = $g->datos()->count();
            $distintos = $g->datos()->distinct('x_value')->count('x_value');
            expect($distintos)->toBe($total, "peso_talla_{$g->sexo} tiene x_values duplicados");
        });
    });

    it('los datos tienen valores SD (-3 a +3) no nulos', function () {
        // Verificar que al menos el 90% de los registros tienen sd0
        $total = OmsDatoGrafica::count();
        $con_sd0 = OmsDatoGrafica::whereNotNull('sd0')->count();

        expect($con_sd0 / $total)->toBeGreaterThanOrEqual(0.9);
    });

    it('los datos tienen percentiles P3, P50 y P97 no nulos', function () {
        $total = OmsDatoGrafica::count();
        $con_p50 = OmsDatoGrafica::whereNotNull('p50')->count();

        expect($con_p50 / $total)->toBeGreaterThanOrEqual(0.9);
    });

    it('es idempotente — ejecutar dos veces no duplica registros', function () {
        $countAntes = OmsCatalogoGrafica::count();
        $datosAntes = OmsDatoGrafica::count();

        $this->seed(WhoDataSeeder::class);

        expect(OmsCatalogoGrafica::count())->toBe($countAntes)
            ->and(OmsDatoGrafica::count())->toBe($datosAntes);
    });
});
