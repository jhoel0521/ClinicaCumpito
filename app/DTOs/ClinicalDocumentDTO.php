<?php

namespace App\DTOs;

use App\ValueObjects\PaperSize;

/**
 * Documento clínico listo para imprimir: datos validados, items y
 * tamaño físico. Las plantillas consumen únicamente este DTO (no tocan
 * la base de datos).
 */
class ClinicalDocumentDTO
{
    /**
     * @param  array<int, string>  $errors
     * @param  array<int, object>  $items
     */
    public function __construct(
        public readonly PaperSize $paper,
        public readonly string $title,
        public readonly string $patientName,
        public readonly string $ageText,
        public readonly string $dateText,
        public readonly string $dateIso,
        public readonly ?string $weight,
        public readonly ?string $height,
        public readonly ?string $diagnosis,
        public readonly ?string $observations,
        public readonly array $items,
        public readonly string $doctorName,
        public readonly string $specialty,
        public readonly ?string $phone,
        public readonly array $errors = [],
        public readonly bool $overflow = false,
    ) {}

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function fileName(): string
    {
        $slug = strtolower(str_replace([' ', '/', '\\', ':', '"', "'", '?', '*', '#'], '-', (string) $this->patientName));
        $slug = preg_replace('/-+/', '-', $slug) ?: 'paciente';

        return $this->title.'_'.$slug.'_'.$this->dateIso.'.pdf';
    }
}
