<?php

namespace App\Contracts;

use App\Models\LaboratoryRequest;
use App\Models\Prescription;

interface ConsultationSnapshotServiceContract
{
    public function snapshotPrescriptionFromTemplate(string $consultationId, string $templateId): Prescription;

    public function snapshotLaboratoryFromTemplate(string $consultationId, string $templateId): LaboratoryRequest;

    public function lockConsultationSnapshots(string $consultationId): void;

    public function canEditLaboratoryResults(string $laboratoryRequestId): bool;
}
