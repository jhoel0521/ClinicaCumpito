<?php

namespace App\Contracts;

use App\Models\LaboratoryItemResult;
use Illuminate\Support\Collection;

interface LaboratoryItemResultServiceContract
{
    /**
     * Registra el resultado de un ítem de laboratorio.
     * Solo permitido mientras la orden esté pendiente.
     *
     * @param  array{value?: string|null, report_text?: string|null, is_abnormal?: bool}  $data
     */
    public function create(string $laboratoryRequestItemId, array $data, ?string $consultationId = null): LaboratoryItemResult;

    /**
     * Elimina un resultado. Solo permitido dentro de la ventana de 3 días
     * desde la creación de la orden (regla de inmutabilidad clínica).
     */
    public function delete(string $laboratoryItemResultId): bool;

    /** @return Collection<int, LaboratoryItemResult> */
    public function listByRequest(string $laboratoryRequestId): Collection;
}
