<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OmsCatalogoGrafica>
 */
class OmsCatalogoGraficaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $variantes = [
            [
                'nombre' => 'Peso para la Talla - Niños',
                'codigo' => 'WHO_WT_LEN_M_0_24M',
                'tipo_grafica' => 'peso_talla',
                'rango_edad' => '0-24 meses',
                'sexo' => 'M',
                'descripcion' => 'Gráfica OMS de peso para la talla en niños de 0 a 24 meses.',
            ],
            [
                'nombre' => 'Peso para la Talla - Niñas',
                'codigo' => 'WHO_WT_LEN_F_0_24M',
                'tipo_grafica' => 'peso_talla',
                'rango_edad' => '0-24 meses',
                'sexo' => 'F',
                'descripcion' => 'Gráfica OMS de peso para la talla en niñas de 0 a 24 meses.',
            ],
            [
                'nombre' => 'Talla para la Edad - Niños',
                'codigo' => 'WHO_LEN_AGE_M_0_5Y',
                'tipo_grafica' => 'talla_edad',
                'rango_edad' => '0-5 años',
                'sexo' => 'M',
                'descripcion' => 'Gráfica OMS de talla para la edad en niños de 0 a 5 años.',
            ],
            [
                'nombre' => 'Perímetro Cefálico - Niñas',
                'codigo' => 'WHO_HC_AGE_F_0_5Y',
                'tipo_grafica' => 'perimetro_cefalico',
                'rango_edad' => '0-5 años',
                'sexo' => 'F',
                'descripcion' => 'Gráfica OMS de perímetro cefálico en niñas de 0 a 5 años.',
            ],
            [
                'nombre' => 'IMC para la Edad - Niños',
                'codigo' => 'WHO_BMI_AGE_M_5_19Y',
                'tipo_grafica' => 'imc',
                'rango_edad' => '5-19 años',
                'sexo' => 'M',
                'descripcion' => 'Gráfica OMS de IMC para la edad en niños de 5 a 19 años.',
            ],
        ];

        $variante = $this->faker->unique()->randomElement($variantes);

        return [
            'nombre' => $variante['nombre'],
            'codigo' => $variante['codigo'],
            'descripcion' => $variante['descripcion'],
            'tipo_grafica' => $variante['tipo_grafica'],
            'rango_edad' => $variante['rango_edad'],
            'sexo' => $variante['sexo'],
            'minimo_z_score' => -3,
            'maximo_z_score' => 3,
            'minimo_percentil' => 3,
            'maximo_percentil' => 97,
        ];
    }
}
