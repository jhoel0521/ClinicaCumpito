<?php

use App\Models\Doctor;
use App\Models\Medication;
use App\Models\PrescriptionTemplate;
use App\Models\User;
use Livewire\Livewire;

describe('Template module', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->doctor = Doctor::factory()->create();
        $this->user->update(['doctor_id' => $this->doctor->id]);
    });

    test('ruta de recetas predefinidas requiere autenticacion', function () {
        $this->get(route('settings.prescriptions'))->assertRedirect(route('login'));
    });

    test('doctor autenticado puede acceder a recetas predefinidas', function () {
        $this->actingAs($this->user)
            ->get(route('settings.prescriptions'))
            ->assertOk();
    });

    test('openCreateModal resetea el formulario y dispara modal-show en recetas', function () {
        $this->actingAs($this->user);

        Livewire::test('pages::templates.prescription-templates')
            ->set('name', 'borrador')
            ->call('openCreateModal')
            ->assertSet('name', '')
            ->assertSet('items', [])
            ->assertSet('editingTemplateId', null)
            ->assertDispatched('modal-show');
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
            ->assertHasNoErrors()
            ->assertDispatched('modal-close');

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
});
