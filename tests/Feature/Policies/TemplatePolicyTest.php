<?php

namespace Tests\Feature\Policies;

use App\Models\Doctor;
use App\Models\PrescriptionTemplate;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->doctor = Doctor::factory()->create();
    $this->user = User::factory()->create(['doctor_id' => $this->doctor->id]);
});

describe('PrescriptionTemplatePolicy', function (): void {
    test('doctor puede crear plantilla de receta', function (): void {
        $this->actingAs($this->user);

        Livewire::test('pages::templates.prescription-templates')
            ->set('name', 'Receta test policy')
            ->set('items', [[
                'custom_medication_name' => 'Amoxicilina 500mg',
                'dose' => '500mg',
                'frequency' => 'cada 8h',
                'duration' => '5 días',
                'instructions' => '',
            ]])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('modal-close');

        $this->assertDatabaseHas('prescription_templates', [
            'doctor_id' => $this->doctor->id,
            'name' => 'Receta test policy',
        ]);
    });

    test('doctor puede eliminar su propia plantilla de receta', function (): void {
        $this->actingAs($this->user);

        $template = PrescriptionTemplate::factory()->create([
            'doctor_id' => $this->doctor->id,
        ]);

        Livewire::test('pages::templates.prescription-templates')
            ->call('delete', $template->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('prescription_templates', ['id' => $template->id]);
    });

    test('usuario sin doctor_id no puede crear plantilla de receta', function (): void {
        $userSinDoctor = User::factory()->create();
        $this->actingAs($userSinDoctor);

        Livewire::test('pages::templates.prescription-templates')
            ->set('name', 'Intento sin doctor')
            ->set('items', [['custom_medication_name' => 'Med X', 'dose' => '1mg', 'frequency' => 'c/8h', 'duration' => '3d', 'instructions' => '']])
            ->call('save')
            ->assertForbidden();
    });
});
