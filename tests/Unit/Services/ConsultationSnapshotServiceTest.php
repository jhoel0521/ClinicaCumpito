<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryTemplate;
use App\Models\LaboratoryTemplateItem;
use App\Models\Patient;
use App\Models\PrescriptionTemplate;
use App\Models\PrescriptionTemplateItem;
use App\Services\ConsultationSnapshotService;
use App\ValueObjects\ConsultationStatus;
use Carbon\Carbon;

describe('ConsultationSnapshotService', function () {
    beforeEach(function () {
        $this->service = new ConsultationSnapshotService;
        $this->doctor = Doctor::factory()->create();
        $this->patient = Patient::factory()->create();
        $this->consultation = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'status' => ConsultationStatus::DRAFT,
        ]);
    });

    test('snapshotPrescriptionFromTemplate copia items de plantilla', function () {
        $template = PrescriptionTemplate::factory()
            ->for($this->doctor)
            ->create(['name' => 'Resfrio']);

        PrescriptionTemplateItem::factory()
            ->for($template, 'template')
            ->create(['dose' => '500mg', 'frequency' => 'cada 8 horas']);

        PrescriptionTemplateItem::factory()
            ->for($template, 'template')
            ->create(['dose' => '200mg', 'frequency' => 'cada 6 horas']);

        $prescription = $this->service->snapshotPrescriptionFromTemplate(
            $this->consultation->id,
            $template->id
        );

        expect($prescription->items())->count()->toBe(2);
        expect($prescription->items->first()->dose)->toBe('500mg');
    });

    test('snapshotLaboratoryFromTemplate copia items de plantilla', function () {
        $template = LaboratoryTemplate::factory()
            ->for($this->doctor)
            ->create(['name' => 'Perfil Básico']);

        LaboratoryTemplateItem::factory()
            ->for($template, 'template')
            ->create(['indications' => 'Ayunas de 8 horas']);

        $request = $this->service->snapshotLaboratoryFromTemplate(
            $this->consultation->id,
            $template->id
        );

        expect($request->items())->count()->toBe(1);
        expect($request->items->first()->indications)->toBe('Ayunas de 8 horas');
    });

    test('lockConsultationSnapshots establece status FINALIZED', function () {
        $this->service->lockConsultationSnapshots($this->consultation->id);

        $this->consultation->refresh();
        expect($this->consultation->status->value())->toBe(ConsultationStatus::FINALIZED);
    });

    test('canEditLaboratoryResults retorna false cuando pasaron 3+ dias', function () {
        $request = \App\Models\LaboratoryRequest::factory()->create([
            'consultation_id' => $this->consultation->id,
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(4),
        ]);

        expect($this->service->canEditLaboratoryResults($request->id))->toBeFalse();
    });

    test('canEditLaboratoryResults retorna true en menos de 3 dias', function () {
        $request = \App\Models\LaboratoryRequest::factory()->create([
            'consultation_id' => $this->consultation->id,
        ]);

        $request->update(['created_at' => Carbon::now()->subDays(1)]);

        expect($this->service->canEditLaboratoryResults($request->id))->toBeTrue();
    });

    test('snapshotPrescriptionFromTemplate falla si consulta esta finalizada', function () {
        $this->consultation->update(['status' => ConsultationStatus::FINALIZED]);

        $template = PrescriptionTemplate::factory()->for($this->doctor)->create();

        $this->service->snapshotPrescriptionFromTemplate(
            $this->consultation->id,
            $template->id
        );
    })->throws(\DomainException::class);
});
