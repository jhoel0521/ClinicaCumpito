<?php

use App\Contracts\ConsultationSnapshotServiceContract;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PrescriptionTemplate;
use App\Models\PrescriptionTemplateItem;
use App\ValueObjects\ConsultationStatus;
use Carbon\Carbon;

describe('ConsultationSnapshotServiceFeature', function () {
    test('flujo completo: aplica plantilla de receta → bloquea consulta como inmutable', function () {
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

        $service = app(ConsultationSnapshotServiceContract::class);

        $prescription = $service->snapshotPrescriptionFromTemplate(
            $consultation->id,
            $prescTemplate->id
        );
        expect($prescription->items()->count())->toBe(1);

        $service->lockConsultationSnapshots($consultation->id);

        $consultation->refresh();
        expect($consultation->status->value())->toBe(ConsultationStatus::FINALIZED);
    });

    test('canEditLaboratoryResults retorna true en menos de 3 dias', function () {
        $consultation = Consultation::factory()->create(['status' => ConsultationStatus::DRAFT]);
        $labRequest = \App\Models\LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        $service = app(ConsultationSnapshotServiceContract::class);
        expect($service->canEditLaboratoryResults($labRequest->id))->toBeTrue();
    });

    test('canEditLaboratoryResults retorna false cuando pasaron 3+ dias', function () {
        $consultation = Consultation::factory()->create(['status' => ConsultationStatus::DRAFT]);
        $labRequest = \App\Models\LaboratoryRequest::factory()->create([
            'consultation_id' => $consultation->id,
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(4),
        ]);

        $service = app(ConsultationSnapshotServiceContract::class);
        expect($service->canEditLaboratoryResults($labRequest->id))->toBeFalse();
    });
});
