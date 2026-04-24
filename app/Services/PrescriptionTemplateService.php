<?php

namespace App\Services;

use App\Contracts\PrescriptionTemplateServiceContract;
use App\DTOs\Templates\PrescriptionTemplateDTO;
use App\Models\PrescriptionTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PrescriptionTemplateService implements PrescriptionTemplateServiceContract
{
    public function createPrescriptionTemplate(PrescriptionTemplateDTO $dto): PrescriptionTemplate
    {
        return DB::transaction(function () use ($dto) {
            $template = PrescriptionTemplate::create($dto->toArray());

            foreach ($dto->items as $itemDto) {
                $template->items()->create($itemDto->toArray());
            }

            return $template;
        });
    }

    public function updatePrescriptionTemplate(string $id, PrescriptionTemplateDTO $dto): PrescriptionTemplate
    {
        return DB::transaction(function () use ($id, $dto) {
            $template = PrescriptionTemplate::findOrFail($id);
            $template->update($dto->toArray());

            $template->items()->delete();

            foreach ($dto->items as $itemDto) {
                $template->items()->create($itemDto->toArray());
            }

            return $template;
        });
    }

    public function deletePrescriptionTemplate(string $id): bool
    {
        return (bool) DB::transaction(fn () => PrescriptionTemplate::findOrFail($id)->delete());
    }

    /**
     * @return Collection<int, PrescriptionTemplate>
     */
    public function getPrescriptionTemplatesByDoctor(string $doctor_id): Collection
    {
        return PrescriptionTemplate::where('doctor_id', $doctor_id)
            ->with('items')
            ->get();
    }
}
