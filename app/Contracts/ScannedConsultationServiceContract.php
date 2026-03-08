<?php

namespace App\Contracts;

use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Http\UploadedFile;

interface ScannedConsultationServiceContract
{
    /**
     * Crea una Consultation de tipo manual con archivo adjunto guardado en storage privado.
     */
    public function createFromScan(
        Patient $patient,
        UploadedFile $file,
        string $consultationDate,
        \App\Models\User $uploader
    ): Consultation;

    /**
     * Elimina el archivo del storage (no borra la Consultation).
     */
    public function deleteFile(Consultation $consultation): void;
}
