<?php

namespace App\Contracts;

use App\ValueObjects\ZScore;

interface ZScoreServiceContract
{
    public function calculateFromLms(float $measurement, float $lValue, float $mValue, float $sValue): ZScore;

    public function calculateByGrafica(string $graficaId, float $xValue, float $measurement): ZScore;
}
