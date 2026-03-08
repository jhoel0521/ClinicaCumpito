<?php

use App\DTOs\Templates\LaboratoryTemplateDTO;
use App\DTOs\Templates\PrescriptionTemplateDTO;
use App\Models\Doctor;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryTemplate;
use App\Models\Medication;
use App\Models\PrescriptionTemplate;
use App\Services\TemplateService;

describe('TemplateService', function () {
    describe('Prescription Templates', function () {
        test('puede crear una plantilla de receta con items', function () {
            $service = new TemplateService;
            $doctor = Doctor::factory()->create();
            $medication = Medication::factory()->create();

            $dto = PrescriptionTemplateDTO::fromArray([
                'doctor_id' => $doctor->id,
                'name' => 'Faringitis aguda',
                'description' => 'Tratamiento base para faringitis',
                'items' => [
                    [
                        'medication_id' => $medication->id,
                        'dose' => '500mg',
                        'frequency' => 'cada 8 horas',
                        'duration' => '7 días',
                    ],
                ],
            ]);

            $template = $service->createPrescriptionTemplate($dto);

            expect($template)->toBeInstanceOf(PrescriptionTemplate::class);
            expect($template->items)->toHaveCount(1);
            expect($template->items->first()->medication_id)->toBe($medication->id);
        })->group('template-service');

        test('puede actualizar una plantilla de receta y sus items', function () {
            $service = new TemplateService;
            $template = PrescriptionTemplate::factory()->create();
            $medication = Medication::factory()->create();

            $dto = PrescriptionTemplateDTO::fromArray([
                'doctor_id' => $template->doctor_id,
                'name' => 'Nombre Actualizado',
                'items' => [
                    [
                        'medication_id' => $medication->id,
                        'dose' => '1g',
                    ],
                ],
            ]);

            $updated = $service->updatePrescriptionTemplate($template->id, $dto);

            expect($updated->name)->toBe('Nombre Actualizado');
            expect($updated->items)->toHaveCount(1);
            expect($updated->items->first()->dose)->toBe('1g');
        })->group('template-service');

        test('puede eliminar una plantilla de receta', function () {
            $service = new TemplateService;
            $template = PrescriptionTemplate::factory()->create();
            $id = $template->id;

            $deleted = $service->deletePrescriptionTemplate($id);

            expect($deleted)->toBeTrue();
            expect(PrescriptionTemplate::find($id))->toBeNull();
        })->group('template-service');
    });

    describe('Laboratory Templates', function () {
        test('puede crear una plantilla de laboratorio con items', function () {
            $service = new TemplateService;
            $doctor = Doctor::factory()->create();
            $exam = LaboratoryExam::factory()->create();

            $dto = LaboratoryTemplateDTO::fromArray([
                'doctor_id' => $doctor->id,
                'name' => 'Perfil Hepático',
                'items' => [
                    [
                        'laboratory_exam_id' => $exam->id,
                        'indications' => 'Ayuno de 8 horas',
                    ],
                ],
            ]);

            $template = $service->createLaboratoryTemplate($dto);

            expect($template)->toBeInstanceOf(LaboratoryTemplate::class);
            expect($template->items)->toHaveCount(1);
            expect($template->items->first()->laboratory_exam_id)->toBe($exam->id);
        })->group('template-service');
    });
});
