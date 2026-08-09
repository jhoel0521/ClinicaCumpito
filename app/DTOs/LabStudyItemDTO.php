<?php

namespace App\DTOs;

/**
 * Un estudio solicitado en la orden de laboratorio, con su categoría
 * para la agrupación en la impresión.
 */
class LabStudyItemDTO
{
    public function __construct(
        public readonly string $exam_name,
        public readonly ?string $parameter_name,
        public readonly string $category,
    ) {}
}
