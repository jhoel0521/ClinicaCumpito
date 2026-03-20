<?php

use App\DTOs\Templates\PrescriptionTemplateDTO;
use App\Models\Doctor;
use App\Models\Medication;
use App\Models\PrescriptionTemplate;
use App\Services\PrescriptionTemplateService;

describe('PrescriptionTemplateService', function () {
    test('puede crear una plantilla de receta con items', function () {
        $service = new PrescriptionTemplateService;
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
        $service = new PrescriptionTemplateService;
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
        $service = new PrescriptionTemplateService;
        $template = PrescriptionTemplate::factory()->create();
        $id = $template->id;

        $deleted = $service->deletePrescriptionTemplate($id);

        expect($deleted)->toBeTrue();
        expect(PrescriptionTemplate::find($id))->toBeNull();
    })->group('template-service');
});
