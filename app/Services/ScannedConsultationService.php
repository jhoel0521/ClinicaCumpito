<?php

namespace App\Services;

use App\Contracts\ScannedConsultationServiceContract;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ScannedConsultationService implements ScannedConsultationServiceContract
{
    public function createFromScan(
        Patient $patient,
        UploadedFile $file,
        string $consultationDate,
        User $uploader
    ): Consultation {
        $consultation = Consultation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $uploader->doctor_id,
            'type' => 'manual',
            'status' => 'draft',
            'consultation_date' => $consultationDate,
            'pending_transcription' => true,
        ]);

        $originalName = $file->getClientOriginalName();
        $path = $file->storeAs("consultations/{$consultation->id}", $originalName, 'local');

        $consultation->update([
            'scanned_file_path' => $path,
            'scanned_file_name' => $originalName,
        ]);

        return $consultation->fresh() ?? $consultation;
    }

    public function deleteFile(Consultation $consultation): void
    {
        if ($consultation->scanned_file_path) {
            Storage::disk('local')->delete($consultation->scanned_file_path);
            $consultation->update([
                'scanned_file_path' => null,
                'scanned_file_name' => null,
            ]);
        }
    }
}
