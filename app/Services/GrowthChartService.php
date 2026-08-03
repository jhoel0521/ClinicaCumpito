<?php

namespace App\Services;

use App\Contracts\GrowthChartServiceContract;
use App\Contracts\ZScoreServiceContract;
use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use App\Models\Patient;
use App\Models\VitalSign;
use App\ValueObjects\Age;

class GrowthChartService implements GrowthChartServiceContract
{
    // Colores semáforo por línea SD
    private const SD_COLORS = [
        '-3 DS' => '#dc2626',
        '-2 DS' => '#f97316',
        '-1 DS' => '#facc15',
        'Mediana' => '#16a34a',
        '+1 DS' => '#facc15',
        '+2 DS' => '#f97316',
        '+3 DS' => '#dc2626',
    ];

    // Curvas de percentiles para modo Padres (P3/P50/P97)
    private const PERCENTILE_DATASETS = [
        ['key' => 'p3',  'label' => 'OMS Mín (P3)',    'color' => 'rgba(239,68,68,0.5)',  'dash' => true],
        ['key' => 'p50', 'label' => 'OMS Ideal (P50)', 'color' => '#16a34a',              'dash' => false],
        ['key' => 'p97', 'label' => 'OMS Máx (P97)',   'color' => 'rgba(239,68,68,0.5)',  'dash' => true],
    ];

    public function __construct(private ZScoreServiceContract $zscoreService) {}

    /** @return array{labels: array<int, float>, datasets: array<int, array{label: string, color: string, data: array<int, float|null>}>, percentile_datasets: array<int, array{label: string, color: string, dash: bool, data: array<int, float|null>}>} */
    public function getReferenceDatasets(string $graficaId): array
    {
        $puntos = OmsDatoGrafica::query()
            ->where('oms_catalogo_grafica_id', $graficaId)
            ->orderBy('x_value')
            ->get();

        if ($puntos->isEmpty()) {
            throw new \InvalidArgumentException(
                "No hay datos OMS para la gráfica '{$graficaId}'."
            );
        }

        $toFloat = fn ($v): ?float => $v !== null ? (float) $v : null;

        $percentileDatasets = array_map(
            fn (array $pd) => [
                'label' => $pd['label'],
                'color' => $pd['color'],
                'dash' => $pd['dash'],
                'data' => $puntos->pluck($pd['key'])->map($toFloat)->all(),
            ],
            self::PERCENTILE_DATASETS,
        );

        return [
            'labels' => $puntos->pluck('x_value')->map(fn ($v) => (float) $v)->all(),
            'datasets' => [
                ['label' => '-3 DS',   'color' => self::SD_COLORS['-3 DS'],   'data' => $puntos->pluck('sd3neg')->map($toFloat)->all()],
                ['label' => '-2 DS',   'color' => self::SD_COLORS['-2 DS'],   'data' => $puntos->pluck('sd2neg')->map($toFloat)->all()],
                ['label' => '-1 DS',   'color' => self::SD_COLORS['-1 DS'],   'data' => $puntos->pluck('sd1neg')->map($toFloat)->all()],
                ['label' => 'Mediana', 'color' => self::SD_COLORS['Mediana'], 'data' => $puntos->pluck('sd0')->map($toFloat)->all()],
                ['label' => '+1 DS',   'color' => self::SD_COLORS['+1 DS'],   'data' => $puntos->pluck('sd1')->map($toFloat)->all()],
                ['label' => '+2 DS',   'color' => self::SD_COLORS['+2 DS'],   'data' => $puntos->pluck('sd2')->map($toFloat)->all()],
                ['label' => '+3 DS',   'color' => self::SD_COLORS['+3 DS'],   'data' => $puntos->pluck('sd3')->map($toFloat)->all()],
            ],
            'percentile_datasets' => $percentileDatasets,
        ];
    }

    /** @return array<int, array{x: float, y: float, z_score: float, category: string, date: string}> */
    public function getPatientDatapoints(string $patientId, string $graficaId): array
    {
        $grafica = OmsCatalogoGrafica::findOrFail($graficaId);
        $patient = Patient::with(['consultations.vitalSigns'])->findOrFail($patientId);

        // Rango X disponible en la boleta OMS: puntos fuera de rango se omiten
        // (el catálogo sembrado cubre 0–60 meses; extrapolar daría z-scores clínicamente erróneos).
        $baseQuery = OmsDatoGrafica::query()->where('oms_catalogo_grafica_id', $graficaId);
        $minXRaw = (clone $baseQuery)->min('x_value');
        $maxXRaw = (clone $baseQuery)->max('x_value');

        $minX = $minXRaw !== null ? (float) $minXRaw : null;
        $maxX = $maxXRaw !== null ? (float) $maxXRaw : null;

        $datapoints = [];

        foreach ($patient->consultations as $consultation) {
            $vitalSign = $consultation->vitalSigns;

            if (! $vitalSign instanceof VitalSign) {
                continue;
            }

            [$xValue, $measurement] = $this->extractMeasurements(
                $grafica->tipo_grafica,
                $vitalSign,
                $patient,
                $consultation->consultation_date,
            );

            if ($xValue === null || $measurement === null) {
                continue;
            }

            // Fuera del rango de la boleta → no graficar (evita puntos falsos en "0 meses")
            if ($minX !== null && $maxX !== null && ($xValue < $minX || $xValue > $maxX)) {
                continue;
            }

            try {
                $zscore = $this->zscoreService->calculateByGrafica($graficaId, $xValue, $measurement);

                $datapoints[] = [
                    'x' => round($xValue, 2),
                    'y' => round($measurement, 2),
                    'z_score' => $zscore->rounded(2),
                    'category' => $zscore->category(),
                    'date' => $consultation->consultation_date->format('Y-m-d'),
                ];
            } catch (\Throwable) {
                // Si no hay datos OMS para ese rango, se omite el punto
                continue;
            }
        }

        return $datapoints;
    }

    /** @return array{grafica: array{id: string, nombre: string, tipo_grafica: string}, labels: array<int, float>, reference_datasets: array<int, array{label: string, color: string, data: array<int, float|null>}>, percentile_datasets: array<int, array{label: string, color: string, dash: bool, data: array<int, float|null>}>, patient_datapoints: array<int, array{x: float, y: float, z_score: float, category: string, date: string}>} */
    public function prepareChartData(string $patientId, string $graficaId): array
    {
        $grafica = OmsCatalogoGrafica::findOrFail($graficaId);
        $reference = $this->getReferenceDatasets($graficaId);

        return [
            'grafica' => [
                'id' => $grafica->id,
                'nombre' => $grafica->nombre,
                'tipo_grafica' => $grafica->tipo_grafica,
            ],
            'labels' => $reference['labels'],
            'reference_datasets' => $reference['datasets'],
            'percentile_datasets' => $reference['percentile_datasets'],
            'patient_datapoints' => $this->getPatientDatapoints($patientId, $graficaId),
        ];
    }

    /**
     * Extrae el par [x_value, measurement] según el tipo de gráfica.
     * Retorna [null, null] si alguna medición requerida no está disponible.
     *
     * Fórmulas LMS OMS:
     *   talla_edad          → x = edad meses, y = talla cm
     *   peso_edad           → x = edad meses, y = peso kg
     *   peso_talla          → x = talla cm,   y = peso kg
     *   perimetro_cefalico  → x = edad meses, y = PC cm
     *
     * @return array{0: float|null, 1: float|null}
     */
    private function extractMeasurements(
        string $tipoGrafica,
        VitalSign $vitalSign,
        Patient $patient,
        \Carbon\CarbonInterface $consultDate,
    ): array {
        // Sin fecha de nacimiento no se puede ubicar la edad en el eje X:
        // se omite el punto en lugar de graficarlo falsamente en "0 meses".
        $ageMonths = $patient->date_of_birth
            ? Age::fromDates($patient->date_of_birth, $consultDate)->months()
            : null;
        $weight = $vitalSign->weight?->value();
        $height = $vitalSign->height?->value();
        $hc = $vitalSign->head_circumference?->value();

        return match ($tipoGrafica) {
            'talla_edad' => [$ageMonths !== null ? (float) $ageMonths : null, $height],
            'peso_edad' => [$ageMonths !== null ? (float) $ageMonths : null, $weight],
            'peso_talla' => [$height, $weight],
            'perimetro_cefalico' => [$ageMonths !== null ? (float) $ageMonths : null, $hc],
            default => [null, null],
        };
    }
}
