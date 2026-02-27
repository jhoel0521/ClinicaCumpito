<?php

namespace Database\Seeders;

use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WhoDataSeeder extends Seeder
{
    private const BASE = 'resources/data who';

    /**
     * 10 configuraciones: 5 tipos × 2 sexos.
     * zscore_files  → array de rutas relativas a BASE, se cargan y fusionan en orden.
     * pctile_files  → igual, para percentiles.
     * x_cutoff      → para peso_talla: umbral en cm donde se corta el primer archivo (wfl).
     *
     * @var array<int, array{tipo: string, sexo: string, codigo: string, nombre: string, rango_edad: string, zscore_files: list<string>, pctile_files: list<string>, x_cutoff?: float}>
     */
    private array $charts = [
        // ── peso_edad ───────────────────────────────────────────────────────────
        [
            'tipo' => 'peso_edad',
            'sexo' => 'M',
            'codigo' => 'peso_edad_M',
            'nombre' => 'Peso para la Edad — Niños',
            'rango_edad' => '0-60 meses',
            'zscore_files' => ['Weight-for-age/wfa_boys_0-to-5-years_zscores.xlsx'],
            'pctile_files' => ['Weight-for-age/tab_wfa_boys_p_0_5.xlsx'],
        ],
        [
            'tipo' => 'peso_edad',
            'sexo' => 'F',
            'codigo' => 'peso_edad_F',
            'nombre' => 'Peso para la Edad — Niñas',
            'rango_edad' => '0-60 meses',
            'zscore_files' => ['Weight-for-age/wfa_girls_0-to-5-years_zscores.xlsx'],
            'pctile_files' => ['Weight-for-age/tab_wfa_girls_p_0_5.xlsx'],
        ],
        // ── talla_edad ──────────────────────────────────────────────────────────
        [
            'tipo' => 'talla_edad',
            'sexo' => 'M',
            'codigo' => 'talla_edad_M',
            'nombre' => 'Talla para la Edad — Niños',
            'rango_edad' => '0-60 meses',
            'zscore_files' => [
                'Lengthheight-for-age/lhfa_boys_0-to-2-years_zscores.xlsx',
                'Lengthheight-for-age/lhfa_boys_2-to-5-years_zscores.xlsx',
            ],
            'pctile_files' => [
                'Lengthheight-for-age/tab_lhfa_boys_p_0_2.xlsx',
                'Lengthheight-for-age/tab_lhfa_boys_p_2_5.xlsx',
            ],
        ],
        [
            'tipo' => 'talla_edad',
            'sexo' => 'F',
            'codigo' => 'talla_edad_F',
            'nombre' => 'Talla para la Edad — Niñas',
            'rango_edad' => '0-60 meses',
            'zscore_files' => [
                'Lengthheight-for-age/lhfa_girls_0-to-2-years_zscores.xlsx',
                'Lengthheight-for-age/lhfa_girls_2-to-5-years_zscores.xlsx',
            ],
            'pctile_files' => [
                'Lengthheight-for-age/tab_lhfa_girls_p_0_2.xlsx',
                'Lengthheight-for-age/tab_lhfa_girls_p_2_5.xlsx',
            ],
        ],
        // ── perimetro_cefalico ──────────────────────────────────────────────────
        [
            'tipo' => 'perimetro_cefalico',
            'sexo' => 'M',
            'codigo' => 'perimetro_cefalico_M',
            'nombre' => 'Perímetro Cefálico para la Edad — Niños',
            'rango_edad' => '0-60 meses',
            'zscore_files' => ['Head circumference for age/hcfa-boys-0-5-zscores.xlsx'],
            'pctile_files' => ['Head circumference for age/tab_hcfa_boys_p_0_5.xlsx'],
        ],
        [
            'tipo' => 'perimetro_cefalico',
            'sexo' => 'F',
            'codigo' => 'perimetro_cefalico_F',
            'nombre' => 'Perímetro Cefálico para la Edad — Niñas',
            'rango_edad' => '0-60 meses',
            'zscore_files' => ['Head circumference for age/hcfa-girls-0-5-zscores.xlsx'],
            'pctile_files' => ['Head circumference for age/tab_hcfa_girls_p_0_5.xlsx'],
        ],
        // ── imc ─────────────────────────────────────────────────────────────────
        [
            'tipo' => 'imc',
            'sexo' => 'M',
            'codigo' => 'imc_M',
            'nombre' => 'IMC para la Edad — Niños',
            'rango_edad' => '0-60 meses',
            'zscore_files' => [
                'Body mass index-for-age (BMI-for-age)/bmi_boys_0-to-2-years_zcores.xlsx',
                'Body mass index-for-age (BMI-for-age)/bmi_boys_2-to-5-years_zscores.xlsx',
            ],
            'pctile_files' => [
                'Body mass index-for-age (BMI-for-age)/tab_bmi_boys_p_0_2.xlsx',
                'Body mass index-for-age (BMI-for-age)/tab_bmi_boys_p_2_5.xlsx',
            ],
        ],
        [
            'tipo' => 'imc',
            'sexo' => 'F',
            'codigo' => 'imc_F',
            'nombre' => 'IMC para la Edad — Niñas',
            'rango_edad' => '0-60 meses',
            'zscore_files' => [
                'Body mass index-for-age (BMI-for-age)/bmi_girls_0-to-2-years_zscores.xlsx',
                'Body mass index-for-age (BMI-for-age)/bmi_girls_2-to-5-years_zscores.xlsx',
            ],
            'pctile_files' => [
                'Body mass index-for-age (BMI-for-age)/tab_bmi_girls_p_0_2.xlsx',
                'Body mass index-for-age (BMI-for-age)/tab_bmi_girls_p_2_5.xlsx',
            ],
        ],
        // ── peso_talla ──────────────────────────────────────────────────────────
        // wfl cubre 45–110.5 cm (longitud, acostado).
        // wfh cubre 65–120 cm (talla, parado).
        // Usamos wfl para x < 65 cm, wfh para x ≥ 65 cm.
        [
            'tipo' => 'peso_talla',
            'sexo' => 'M',
            'codigo' => 'peso_talla_M',
            'nombre' => 'Peso para la Talla — Niños',
            'rango_edad' => '45-120 cm',
            'zscore_files' => [
                'Weight-for-lengthheight/wfl_boys_0-to-2-years_zscores.xlsx',
                'Weight-for-lengthheight/wfh_boys_2-to-5-years_zscores.xlsx',
            ],
            'pctile_files' => [
                'Weight-for-lengthheight/tab_wfl_boys_p_0_2.xlsx',
                'Weight-for-lengthheight/tab_wfh_boys_p_2_5.xlsx',
            ],
            'x_cutoff' => 65.0, // cm: archivos[0] se usan solo hasta x < 65
        ],
        [
            'tipo' => 'peso_talla',
            'sexo' => 'F',
            'codigo' => 'peso_talla_F',
            'nombre' => 'Peso para la Talla — Niñas',
            'rango_edad' => '45-120 cm',
            'zscore_files' => [
                'Weight-for-lengthheight/wfl_girls_0-to-2-years_zscores.xlsx',
                'Weight-for-lengthheight/wfh_girls_2-to-5-years_zscores.xlsx',
            ],
            'pctile_files' => [
                'Weight-for-lengthheight/tab_wfl_girls_p_0_2.xlsx',
                'Weight-for-lengthheight/tab_wfh_girls_p_2_5.xlsx',
            ],
            'x_cutoff' => 65.0,
        ],
    ];

    public function run(): void
    {
        foreach ($this->charts as $cfg) {
            $grafica = OmsCatalogoGrafica::updateOrCreate(
                ['codigo' => $cfg['codigo']],
                [
                    'nombre' => $cfg['nombre'],
                    'tipo_grafica' => $cfg['tipo'],
                    'sexo' => $cfg['sexo'],
                    'rango_edad' => $cfg['rango_edad'],
                ]
            );

            $data = $this->buildData(
                $cfg['zscore_files'],
                $cfg['pctile_files'],
                $cfg['x_cutoff'] ?? null,
            );

            foreach ($data as $row) {
                OmsDatoGrafica::updateOrCreate(
                    ['oms_catalogo_grafica_id' => $grafica->id, 'x_value' => $row['x_value']],
                    array_merge($row, ['oms_catalogo_grafica_id' => $grafica->id]),
                );
            }

            if ($this->command !== null) {
                $this->command->info("✔ {$cfg['codigo']}: ".count($data).' puntos');
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Construcción de datos
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Fusiona z-scores y percentiles de múltiples archivos.
     * Para peso_talla (x_cutoff): archivos[0] solo aporta x < cutoff,
     *                              archivos[1] aporta x ≥ cutoff.
     * Para el resto: archivos posteriores sobreescriben valores en x duplicadas (merge 0-2 + 2-5).
     *
     * @param  list<string>  $zFiles
     * @param  list<string>  $pFiles
     * @return array<int, array<string, float|null>>
     */
    private function buildData(array $zFiles, array $pFiles, ?float $xCutoff): array
    {
        // ── 1. Cargar z-scores ────────────────────────────────────────────────
        // Usamos clave string para evitar que PHP convierta floats a int al indexar arrays.
        // PHP convierte silenciosamente float → int en claves de array (ej. 45.5 → 45).
        /** @var array<string, array<string, float|null>> $zIndex */
        $zIndex = [];
        foreach ($zFiles as $i => $relPath) {
            $rows = $this->readZScores(base_path(self::BASE.'/'.$relPath));
            foreach ($rows as $row) {
                $x = (float) $row['x'];
                $xKey = (string) $x;
                if ($xCutoff !== null) {
                    // Archivo 0 = wfl: solo x < cutoff. Archivo 1 = wfh: solo x ≥ cutoff.
                    if ($i === 0 && $x >= $xCutoff) {
                        continue;
                    }
                    if ($i > 0 && $x < $xCutoff) {
                        continue;
                    }
                }
                $zIndex[$xKey] = [
                    'x_value' => $x,
                    'l_value' => $row['l'] !== null ? (float) $row['l'] : null,
                    'm_value' => $row['m'] !== null ? (float) $row['m'] : null,
                    's_value' => $row['s'] !== null ? (float) $row['s'] : null,
                    'sd3neg' => $row['sd3neg'] !== null ? (float) $row['sd3neg'] : null,
                    'sd2neg' => $row['sd2neg'] !== null ? (float) $row['sd2neg'] : null,
                    'sd1neg' => $row['sd1neg'] !== null ? (float) $row['sd1neg'] : null,
                    'sd0' => $row['sd0'] !== null ? (float) $row['sd0'] : null,
                    'sd1' => $row['sd1'] !== null ? (float) $row['sd1'] : null,
                    'sd2' => $row['sd2'] !== null ? (float) $row['sd2'] : null,
                    'sd3' => $row['sd3'] !== null ? (float) $row['sd3'] : null,
                    'p3' => null,
                    'p15' => null,
                    'p50' => null,
                    'p85' => null,
                    'p97' => null,
                ];
            }
        }

        // ── 2. Fusionar percentiles ───────────────────────────────────────────
        foreach ($pFiles as $i => $relPath) {
            $rows = $this->readPercentiles(base_path(self::BASE.'/'.$relPath));
            foreach ($rows as $row) {
                $x = (float) $row['x'];
                $xKey = (string) $x;
                if ($xCutoff !== null) {
                    if ($i === 0 && $x >= $xCutoff) {
                        continue;
                    }
                    if ($i > 0 && $x < $xCutoff) {
                        continue;
                    }
                }
                if (isset($zIndex[$xKey])) {
                    $zIndex[$xKey]['p3'] = $row['p3'] !== null ? (float) $row['p3'] : null;
                    $zIndex[$xKey]['p15'] = $row['p15'] !== null ? (float) $row['p15'] : null;
                    $zIndex[$xKey]['p50'] = $row['p50'] !== null ? (float) $row['p50'] : null;
                    $zIndex[$xKey]['p85'] = $row['p85'] !== null ? (float) $row['p85'] : null;
                    $zIndex[$xKey]['p97'] = $row['p97'] !== null ? (float) $row['p97'] : null;
                }
            }
        }

        ksort($zIndex);

        return array_values($zIndex);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Lectura de Excel
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Lee un archivo z-scores y retorna filas normalizadas.
     * Detecta dinámicamente el nombre de la columna del eje X (Month/Length/Height)
     * y omite la columna extra "SD" presente en archivos lhfa/hcfa.
     *
     * @return list<array{x: string|int|float|null, l: string|int|float|null, m: string|int|float|null, s: string|int|float|null, sd3neg: string|int|float|null, sd2neg: string|int|float|null, sd1neg: string|int|float|null, sd0: string|int|float|null, sd1: string|int|float|null, sd2: string|int|float|null, sd3: string|int|float|null}>
     */
    private function readZScores(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        // Primera fila = encabezados
        /** @var list<string|null> $rawHeader */
        $rawHeader = array_shift($rows);
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $rawHeader);

        // El eje X siempre está en la primera columna (Month | Length | Height)
        $xKey = $header[0] ?? 'month';

        $results = [];
        foreach ($rows as $row) {
            $first = $row[0];
            if ($first === null || $first === '' || ! is_numeric($first)) {
                continue;
            }

            $mapped = [];
            foreach ($header as $idx => $col) {
                $mapped[$col] = $row[$idx] ?? null;
            }

            $results[] = [
                'x' => $mapped[$xKey],
                'l' => $mapped['l'] ?? null,
                'm' => $mapped['m'] ?? null,
                's' => $mapped['s'] ?? null,
                'sd3neg' => $mapped['sd3neg'] ?? null,
                'sd2neg' => $mapped['sd2neg'] ?? null,
                'sd1neg' => $mapped['sd1neg'] ?? null,
                'sd0' => $mapped['sd0'] ?? null,
                'sd1' => $mapped['sd1'] ?? null,
                'sd2' => $mapped['sd2'] ?? null,
                'sd3' => $mapped['sd3'] ?? null,
            ];
        }

        return $results;
    }

    /**
     * Lee un archivo de percentiles y retorna filas normalizadas.
     * Se extraen solo P3, P15, P50, P85, P97.
     *
     * @return list<array{x: string|int|float|null, p3: string|int|float|null, p15: string|int|float|null, p50: string|int|float|null, p85: string|int|float|null, p97: string|int|float|null}>
     */
    private function readPercentiles(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        /** @var list<string|null> $rawHeader */
        $rawHeader = array_shift($rows);
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $rawHeader);

        $xKey = $header[0] ?? 'month';

        $results = [];
        foreach ($rows as $row) {
            $first = $row[0];
            if ($first === null || $first === '' || ! is_numeric($first)) {
                continue;
            }

            $mapped = [];
            foreach ($header as $idx => $col) {
                $mapped[$col] = $row[$idx] ?? null;
            }

            $results[] = [
                'x' => $mapped[$xKey],
                'p3' => $mapped['p3'] ?? null,
                'p15' => $mapped['p15'] ?? null,
                'p50' => $mapped['p50'] ?? null,
                'p85' => $mapped['p85'] ?? null,
                'p97' => $mapped['p97'] ?? null,
            ];
        }

        return $results;
    }
}
