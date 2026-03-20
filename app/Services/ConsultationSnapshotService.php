<?php

namespace App\Services;

use App\Contracts\ConsultationSnapshotServiceContract;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryTemplate;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionTemplate;
use App\ValueObjects\ConsultationStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConsultationSnapshotService implements ConsultationSnapshotServiceContract
{
    public function snapshotPrescriptionFromTemplate(string $consultationId, string $templateId): Prescription
    {
        return DB::transaction(function () use ($consultationId, $templateId): Prescription {
            $consultation = Consultation::findOrFail($consultationId);
            $template = PrescriptionTemplate::findOrFail($templateId);

            if ($this->isConsultationFinalized($consultation)) {
                throw new \DomainException('No se puede agregar plantillas a una consulta finalizada.');
            }

            $prescription = Prescription::firstOrCreate(
                ['consultation_id' => $consultationId]
            );

            $template->items()->each(function ($templateItem) use ($prescription) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medication_name' => $templateItem->custom_medication_name ?? $templateItem->medication?->name,
                    'dose' => $templateItem->dose,
                    'frequency' => $templateItem->frequency,
                    'duration' => $templateItem->duration,
                    'instructions' => $templateItem->instructions,
                ]);
            });

            $fresh = $prescription->fresh(['items']);
            if (! $fresh instanceof Prescription) {
                throw new \RuntimeException('No se pudo refrescar la receta snapshot.');
            }

            return $fresh;
        });
    }

    public function snapshotLaboratoryFromTemplate(string $consultationId, string $templateId): LaboratoryRequest
    {
        return DB::transaction(function () use ($consultationId, $templateId): LaboratoryRequest {
            $consultation = Consultation::findOrFail($consultationId);
            $template = LaboratoryTemplate::findOrFail($templateId);

            if ($this->isConsultationFinalized($consultation)) {
                throw new \DomainException('No se puede agregar plantillas de laboratorio a una consulta finalizada.');
            }

            $request = LaboratoryRequest::create(
                ['consultation_id' => $consultationId]
            );

            $template->items()->each(function ($templateItem) use ($request) {
                LaboratoryRequestItem::create([
                    'laboratory_request_id' => $request->id,
                    'exam_name' => $templateItem->exam?->name,
                ]);
            });

            $fresh = $request->fresh(['items']);
            if (! $fresh instanceof LaboratoryRequest) {
                throw new \RuntimeException('No se pudo refrescar la solicitud de laboratorio snapshot.');
            }

            return $fresh;
        });
    }

    public function lockConsultationSnapshots(string $consultationId): void
    {
        DB::transaction(function () use ($consultationId) {
            $consultation = Consultation::findOrFail($consultationId);

            if ($this->isConsultationFinalized($consultation)) {
                return;
            }

            $consultation->update(['status' => ConsultationStatus::FINALIZED]);
        });
    }

    public function canEditLaboratoryResults(string $laboratoryRequestId): bool
    {
        $request = LaboratoryRequest::findOrFail($laboratoryRequestId);

        $createdDate = $request->created_at;
        if (! $createdDate instanceof \DateTimeInterface) {
            return false;
        }

        $now = Carbon::now();
        $daysPassed = Carbon::instance($createdDate)->diffInDays($now);

        return $daysPassed < 3;
    }

    private function isConsultationFinalized(Consultation $consultation): bool
    {
        $status = $consultation->status instanceof ConsultationStatus
            ? $consultation->status->value()
            : (string) $consultation->status;

        return $status === ConsultationStatus::FINALIZED;
    }
}
