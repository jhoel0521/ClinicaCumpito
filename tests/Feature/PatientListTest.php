<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $doctor = Doctor::factory()->create();
    $this->user = User::factory()->create(['doctor_id' => $doctor->id]);
    $this->actingAs($this->user);
});

describe('PatientList - Listado mejorado (6.3)', function (): void {
    it('muestra pacientes con conteo de consultas', function (): void {
        $patient = Patient::factory()->create(['full_name' => 'Juan Pérez']);
        Consultation::factory()->count(3)->create([
            'patient_id' => $patient->id,
            'doctor_id' => $this->user->doctor_id,
        ]);

        $response = $this->get(route('pacientes.index'));

        $response->assertOk()
            ->assertSee('Juan Pérez');
    });

    it('muestra badge "Incompleto" para pacientes sin datos básicos', function (): void {
        Patient::create(['full_name' => 'Sin Datos']);

        $response = $this->get(route('pacientes.index'));

        $response->assertOk()
            ->assertSee('Sin Datos')
            ->assertSee('Incompleto');
    });

    it('muestra badge "Completo" para pacientes con datos básicos', function (): void {
        Patient::factory()->create(['full_name' => 'Completo Total']);

        $response = $this->get(route('pacientes.index'));

        $response->assertOk()
            ->assertSee('Completo Total')
            ->assertSee('Completo');
    });

    it('no rompe la vista de edición con paciente incompleto', function (): void {
        $patient = Patient::create(['full_name' => 'Solo Nombre']);

        $response = $this->get(route('pacientes.edit', $patient->id));

        $response->assertOk()
            ->assertSee('Solo Nombre');
    });

    it('muestra alerta de datos incompletos en edición con require_complete', function (): void {
        $patient = Patient::create(['full_name' => 'Incompleto']);

        $response = $this->get(route('pacientes.edit', $patient->id).'?require_complete=1');

        $response->assertOk()
            ->assertSee('Datos incompletos');
    });

    it('busca escribiendo solo el apellido "aguilar"', function (): void {
        Patient::factory()->create(['full_name' => 'Aitana Aguilar']);
        Patient::factory()->create(['full_name' => 'Carlos Mendoza']);

        Livewire::test('patient-list')
            ->set('search', 'aguilar')
            ->assertSee('Aitana Aguilar')
            ->assertDontSee('Carlos Mendoza');
    });

    it('busca escribiendo "aguilar aitana" (apellido primero)', function (): void {
        Patient::factory()->create(['full_name' => 'Aitana Aguilar']);
        Patient::factory()->create(['full_name' => 'Bruno Aguilar']);
        Patient::factory()->create(['full_name' => 'Carlos Mendoza']);

        Livewire::test('patient-list')
            ->set('search', 'aguilar aitana')
            ->assertSee('Aitana Aguilar')
            ->assertDontSee('Bruno Aguilar')
            ->assertDontSee('Carlos Mendoza');
    });

    it('busca escribiendo "aitana aguilar" (nombre primero)', function (): void {
        Patient::factory()->create(['full_name' => 'Aitana Aguilar']);
        Patient::factory()->create(['full_name' => 'Aitana Rojas']);
        Patient::factory()->create(['full_name' => 'Bruno Aguilar']);

        Livewire::test('patient-list')
            ->set('search', 'aitana aguilar')
            ->assertSee('Aitana Aguilar')
            ->assertDontSee('Aitana Rojas')
            ->assertDontSee('Bruno Aguilar');
    });
});
