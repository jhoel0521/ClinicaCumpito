<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\ScannedConsultationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('ScannedConsultationService', function () {
    beforeEach(function () {
        Storage::fake('local');
        $this->service = new ScannedConsultationService;

        $doctor = Doctor::factory()->create();
        $this->uploader = User::factory()->create(['doctor_id' => $doctor->id]);
        $this->patient = Patient::factory()->create();
    });

    it('crea una Consultation con tipo manual y status draft', function () {
        $file = UploadedFile::fake()->create('historia.pdf', 500, 'application/pdf');
        $date = now()->subDays(5)->toDateString();

        $consultation = $this->service->createFromScan($this->patient, $file, $date, $this->uploader);

        expect($consultation)->toBeInstanceOf(Consultation::class)
            ->and($consultation->type->value())->toBe('manual')
            ->and($consultation->status->value())->toBe('draft')
            ->and($consultation->patient_id)->toBe($this->patient->id)
            ->and($consultation->doctor_id)->toBe($this->uploader->doctor_id)
            ->and($consultation->pending_transcription)->toBeTrue();
    });

    it('guarda el archivo en el storage local bajo el path consultations/{id}/{nombre}', function () {
        $file = UploadedFile::fake()->create('historia.pdf', 500, 'application/pdf');
        $date = now()->subDays(5)->toDateString();

        $consultation = $this->service->createFromScan($this->patient, $file, $date, $this->uploader);

        expect($consultation->scanned_file_name)->toBe('historia.pdf')
            ->and($consultation->scanned_file_path)->toBe("consultations/{$consultation->id}/historia.pdf");

        Storage::disk('local')->assertExists("consultations/{$consultation->id}/historia.pdf");
    });

    it('deleteFile elimina el archivo del storage y limpia las columnas', function () {
        $file = UploadedFile::fake()->create('scan.jpg', 200, 'image/jpeg');
        $date = now()->toDateString();

        $consultation = $this->service->createFromScan($this->patient, $file, $date, $this->uploader);
        $path = $consultation->scanned_file_path;

        Storage::disk('local')->assertExists($path);

        $this->service->deleteFile($consultation);

        Storage::disk('local')->assertMissing($path);

        expect($consultation->fresh()->scanned_file_path)->toBeNull()
            ->and($consultation->fresh()->scanned_file_name)->toBeNull();
    });
});
