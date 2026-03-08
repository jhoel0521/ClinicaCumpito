<?php

namespace App\Services;

use App\Contracts\TemplateServiceContract;
use App\DTOs\Templates\LaboratoryTemplateDTO;
use App\DTOs\Templates\PrescriptionTemplateDTO;
use App\Models\LaboratoryTemplate;
use App\Models\PrescriptionTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TemplateService implements TemplateServiceContract
{
    // Prescription Templates
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

            // Simple delete-and-recreate for items to handle updates/adds/deletes easily
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
            ->with('items.medication')
            ->get();
    }

    // Laboratory Templates
    public function createLaboratoryTemplate(LaboratoryTemplateDTO $dto): LaboratoryTemplate
    {
        return DB::transaction(function () use ($dto) {
            $template = LaboratoryTemplate::create($dto->toArray());

            foreach ($dto->items as $itemDto) {
                $template->items()->create($itemDto->toArray());
            }

            return $template;
        });
    }

    public function updateLaboratoryTemplate(string $id, LaboratoryTemplateDTO $dto): LaboratoryTemplate
    {
        return DB::transaction(function () use ($id, $dto) {
            $template = LaboratoryTemplate::findOrFail($id);
            $template->update($dto->toArray());

            $template->items()->delete();

            foreach ($dto->items as $itemDto) {
                $template->items()->create($itemDto->toArray());
            }

            return $template;
        });
    }

    public function deleteLaboratoryTemplate(string $id): bool
    {
        return (bool) DB::transaction(fn () => LaboratoryTemplate::findOrFail($id)->delete());
    }

    /**
     * @return Collection<int, LaboratoryTemplate>
     */
    public function getLaboratoryTemplatesByDoctor(string $doctor_id): Collection
    {
        return LaboratoryTemplate::where('doctor_id', $doctor_id)
            ->with('items.exam')
            ->get();
    }
}
