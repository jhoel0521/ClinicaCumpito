<?php

use App\Contracts\ConsultationSnapshotServiceContract;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryTemplate;
use App\Models\LaboratoryTemplateItem;
use App\Models\Patient;
use App\Models\PrescriptionTemplate;
use App\Models\PrescriptionTemplateItem;
use App\ValueObjects\ConsultationStatus;
use Carbon\Carbon;

describe('ConsultationSnapshotServiceFeature', function () {
    test('flujo completo: aplica plantilla → snapshots → bloquea inmutable', function () {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $consultation = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => ConsultationStatus::DRAFT,
        ]);

        $prescTemplate = PrescriptionTemplate::factory()
            ->for($doctor)
            ->create(['name' => 'Resfrio tratamiento']);
        PrescriptionTemplateItem::factory()
            ->for($prescTemplate, 'template')
            ->create(['dose' => '250mg', 'frequency' => 'cada 8 horas']);

        $labTemplate = LaboratoryTemplate::factory()
            ->for($doctor)
            ->create(['name' => 'Panel infeccioso']);
        LaboratoryTemplateItem::factory()
            ->for($labTemplate, 'template')
            ->create();

        $service = app(ConsultationSnapshotServiceContract::class);

        $prescription = $service->snapshotPrescriptionFromTemplate(
            $consultation->id,
            $prescTemplate->id
        );
        expect($prescription->items()->count())->toBe(1);

        $labRequest = $service->snapshotLaboratoryFromTemplate(
            $consultation->id,
            $labTemplate->id
        );
        expect($labRequest->items()->count())->toBe(1);

        $service->lockConsultationSnapshots($consultation->id);

        $consultation->refresh();
        expect($consultation->status->value())->toBe(ConsultationStatus::FINALIZED);

        $service = app(ConsultationSnapshotServiceContract::class);
        expect($service->canEditLaboratoryResults($labRequest->id))->toBeTrue();

        $consultation2 = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => ConsultationStatus::DRAFT,
        ]);
        $labRequest2 = \App\Models\LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation2->id,
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(4),
        ]);
        expect($service->canEditLaboratoryResults($labRequest2->id))->toBeFalse();
    });
});
