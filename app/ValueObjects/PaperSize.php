<?php

namespace App\ValueObjects;

class PaperSize
{
    /** Hoja oficio completa: 215,9 × 330,2 mm */
    public const LEGAL = ['width' => 215.9, 'height' => 330.2];

    /** Media hoja oficio: 215,9 × 165,1 mm */
    public const HALF_LEGAL = ['width' => 215.9, 'height' => 165.1];

    private function __construct(
        public readonly float $widthMm,
        public readonly float $heightMm,
        public readonly float $marginMm,
    ) {}

    public static function halfLegal(float $marginMm = 8): self
    {
        return new self(self::HALF_LEGAL['width'], self::HALF_LEGAL['height'], $marginMm);
    }

    public static function legal(float $marginMm = 10): self
    {
        return new self(self::LEGAL['width'], self::LEGAL['height'], $marginMm);
    }

    /** @return array{0: int, 1: int, 2: float, 3: float} Formato dompdf (puntos + mm) */
    public function toDompdf(): array
    {
        return [0, 0, $this->widthMm, $this->heightMm];
    }
}
