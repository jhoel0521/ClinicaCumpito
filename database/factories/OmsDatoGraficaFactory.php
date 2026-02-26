<?php

namespace Database\Factories;

use App\Models\OmsCatalogoGrafica;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OmsDatoGrafica>
 */
class OmsDatoGraficaFactory extends Factory
{
    /**
     * Define the model's default state.
     * Valores basados en tablas LMS de peso-para-edad (niños 0-24 meses) OMS.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Puntos representativos de la curva LMS (peso-para-edad, niños)
        // Fuente: OMS Child Growth Standards – wfa-boys-0-5-zscores
        $puntos = [
            ['x' => 0.0,  'L' => 0.3487,   'M' => 3.3464,  'S' => 0.14602],
            ['x' => 1.0,  'L' => 0.2297,   'M' => 4.4709,  'S' => 0.13395],
            ['x' => 2.0,  'L' => 0.1970,   'M' => 5.5675,  'S' => 0.12385],
            ['x' => 3.0,  'L' => 0.1738,   'M' => 6.3762,  'S' => 0.11727],
            ['x' => 6.0,  'L' => 0.1128,   'M' => 7.9340,  'S' => 0.11090],
            ['x' => 12.0, 'L' => 0.0139,   'M' => 9.6479,  'S' => 0.11243],
            ['x' => 24.0, 'L' => -0.1220,  'M' => 12.2159, 'S' => 0.11690],
        ];

        $punto = $this->faker->randomElement($puntos);
        $M = $punto['M'];

        return [
            'oms_catalogo_grafica_id' => OmsCatalogoGrafica::factory(),
            'x_value' => $punto['x'],
            'l_value' => $punto['L'],
            'm_value' => $M,
            's_value' => $punto['S'],
            'sd3neg' => round($M * 0.78, 4),
            'sd2neg' => round($M * 0.85, 4),
            'sd1neg' => round($M * 0.92, 4),
            'sd0' => round($M, 4),
            'sd1' => round($M * 1.09, 4),
            'sd2' => round($M * 1.19, 4),
            'sd3' => round($M * 1.30, 4),
            'p3' => round($M * 0.86, 4),
            'p15' => round($M * 0.91, 4),
            'p50' => round($M, 4),
            'p85' => round($M * 1.10, 4),
            'p97' => round($M * 1.20, 4),
        ];
    }
}
