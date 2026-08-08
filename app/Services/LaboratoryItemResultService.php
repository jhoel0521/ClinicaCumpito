<?php

namespace App\Services;

use App\Contracts\ConsultationSnapshotServiceContract;
use App\Contracts\LaboratoryItemResultServiceContract;
use App\Models\LaboratoryItemResult;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use DomainException;
use Illuminate\Support\Collection;

class LaboratoryItemResultService implements LaboratoryItemResultServiceContract
{
    public function __construct(private ConsultationSnapshotServiceContract $snapshots) {}

    public function create(string $laboratoryRequestItemId, array $data, ?string $consultationId = null): LaboratoryItemResult
    {
        $item = LaboratoryRequestItem::with('laboratoryRequest')->findOrFail($laboratoryRequestItemId);

        if (! $item->laboratoryRequest) {
            throw new DomainException('El ítem no pertenece a una orden de laboratorio.');
        }

        $request = $item->laboratoryRequest;

        $this->ensureOrderAllowsResults($request);

        return LaboratoryItemResult::create([
            'laboratory_request_item_id' => $laboratoryRequestItemId,
            'consultation_id' => $consultationId ?: $request->consultation_id,
            'value' => $data['value'] ?? null,
            'report_text' => $data['report_text'] ?? null,
            'is_abnormal' => (bool) ($data['is_abnormal'] ?? false),
            'sort_order' => $item->results()->count(),
        ]);
    }

    public function delete(string $laboratoryItemResultId): bool
    {
        $result = LaboratoryItemResult::with('item.laboratoryRequest')->findOrFail($laboratoryItemResultId);

        $item = $result->item;

        if (! $item || ! $item->laboratoryRequest) {
            throw new DomainException('El ítem no pertenece a una orden de laboratorio.');
        }

        $request = $item->laboratoryRequest;

        $this->ensureOrderAllowsResults($request);

        if (! $this->snapshots->canEditLaboratoryResults($request->id)) {
            throw new DomainException('La ventana de 3 días para editar resultados ha expirado.');
        }

        return (bool) $result->delete();
    }

    public function listByRequest(string $laboratoryRequestId): Collection
    {
        return LaboratoryItemResult::query()
            ->whereHas('item', fn ($q) => $q->where('laboratory_request_id', $laboratoryRequestId))
            ->with('item')
            ->orderBy('sort_order')
            ->get();
    }

    private function ensureOrderAllowsResults(LaboratoryRequest $request): void
    {
        if ($request->isReceived()) {
            throw new DomainException('No se pueden modificar resultados de un laboratorio recibido.');
        }
    }
}
