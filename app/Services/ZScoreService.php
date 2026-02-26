<?php

namespace App\Services;

use App\Contracts\ZScoreServiceContract;
use App\Models\OmsDatoGrafica;
use App\ValueObjects\ZScore;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ZScoreService implements ZScoreServiceContract
{
    public function calculateFromLms(float $measurement, float $lValue, float $mValue, float $sValue): ZScore
    {
        if ($measurement <= 0.0) {
            throw new \InvalidArgumentException('La medición debe ser mayor a 0.');
        }

        if ($mValue <= 0.0 || $sValue <= 0.0) {
            throw new \InvalidArgumentException('Los parámetros M y S deben ser mayores a 0.');
        }

        if (abs($lValue) < 0.000001) {
            $zScore = log($measurement / $mValue) / $sValue;

            return ZScore::make($zScore);
        }

        $zScore = (pow($measurement / $mValue, $lValue) - 1) / ($lValue * $sValue);

        return ZScore::make($zScore);
    }

    public function calculateByGrafica(string $graficaId, float $xValue, float $measurement): ZScore
    {
        $puntos = OmsDatoGrafica::query()
            ->where('oms_catalogo_grafica_id', $graficaId)
            ->get();

        if ($puntos->isEmpty()) {
            throw (new ModelNotFoundException)->setModel(OmsDatoGrafica::class, [$graficaId]);
        }

        $punto = $puntos
            ->sortBy(fn (OmsDatoGrafica $dato): float => abs((float) $dato->x_value - $xValue))
            ->first();

        if (! $punto instanceof OmsDatoGrafica) {
            throw (new ModelNotFoundException)->setModel(OmsDatoGrafica::class, [$graficaId]);
        }

        return $this->calculateFromLms(
            measurement: $measurement,
            lValue: (float) $punto->l_value,
            mValue: (float) $punto->m_value,
            sValue: (float) $punto->s_value,
        );
    }
}
