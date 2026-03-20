<?php

namespace App\Contracts;

use App\Models\Prescription;

interface ConsultationSnapshotServiceContract
{
    public function snapshotPrescriptionFromTemplate(string $consultationId, string $templateId): Prescription;

    public function lockConsultationSnapshots(string $consultationId): void;

    public function canEditLaboratoryResults(string $laboratoryRequestId): bool;
}
