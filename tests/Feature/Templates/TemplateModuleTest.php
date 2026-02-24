<?php

use App\Models\Doctor;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\Medication;
use App\Models\PrescriptionTemplate;
use App\Models\User;
use Livewire\Livewire;

describe('Template module', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->doctor = Doctor::factory()->create(['user_id' => $this->user->id]);
    });

    test('rutas de plantillas requieren autenticacion', function () {
        $this->get(route('templates.index'))->assertRedirect(route('login'));
        $this->get(route('templates.prescriptions'))->assertRedirect(route('login'));
        $this->get(route('templates.laboratories'))->assertRedirect(route('login'));
    });

    test('doctor autenticado puede acceder al modulo de plantillas', function () {
        $this->actingAs($this->user)
            ->get(route('templates.index'))
            ->assertOk();

        $this->actingAs($this->user)
            ->get(route('templates.prescriptions'))
            ->assertOk();

        $this->actingAs($this->user)
            ->get(route('templates.laboratories'))
            ->assertOk();
    });

    test('puede crear plantilla de receta con items', function () {
        $this->actingAs($this->user);

        $medication = Medication::factory()->create();

        Livewire::test('pages::templates.prescription-templates')
            ->set('name', 'Receta de prueba')
            ->set('description', 'Plantilla para pruebas')
            ->set('isActive', true)
            ->set('items', [[
                'medication_id' => $medication->id,
                'custom_medication_name' => '',
                'dose' => '500mg',
                'frequency' => 'cada 8h',
                'duration' => '5 días',
                'instructions' => 'Con alimentos',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('prescription_templates', [
            'doctor_id' => $this->doctor->id,
            'name' => 'Receta de prueba',
        ]);

        $template = PrescriptionTemplate::where('doctor_id', $this->doctor->id)
            ->where('name', 'Receta de prueba')
            ->firstOrFail();

        $this->assertDatabaseHas('prescription_template_items', [
            'template_id' => $template->id,
            'medication_id' => $medication->id,
        ]);
    });

    test('puede crear plantilla de laboratorio con items', function () {
        $this->actingAs($this->user);

        $category = LaboratoryCategory::factory()->create();
        $exam = LaboratoryExam::factory()->create([
            'category_id' => $category->id,
        ]);

        Livewire::test('pages::templates.laboratory-templates')
            ->set('name', 'Laboratorio de prueba')
            ->set('description', 'Plantilla para estudios clínicos')
            ->set('isActive', true)
            ->set('items', [[
                'laboratory_exam_id' => $exam->id,
                'indications' => 'Ayuno de 8 horas',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('laboratory_templates', [
            'doctor_id' => $this->doctor->id,
            'name' => 'Laboratorio de prueba',
        ]);

        $this->assertDatabaseHas('laboratory_template_items', [
            'laboratory_exam_id' => $exam->id,
            'indications' => 'Ayuno de 8 horas',
        ]);
    });
});
