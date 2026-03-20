<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientVaccine;
use App\Models\Prescription;
use App\Models\SoapNote;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\VitalSign;

describe('ConsultationController', function () {
    test('usuario autenticado puede ver lista de consultas', function () {
        Consultation::factory()->count(3)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('consultas.index'))
            ->assertOk();
    });

    test('usuario no autenticado es redirigido a login', function () {
        $response = $this->get(route('consultas.index'));

        $response->assertRedirect(route('login'));
    });

    test('puede crear una consulta con datos validos', function () {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $patient = Patient::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('consultas.store'), [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'type' => 'digital',
                'status' => 'saved',
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'pending_transcription' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('consultations', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'type' => 'digital',
            'status' => 'draft',
        ]);
    });

    test('falla validacion al crear consulta sin paciente', function () {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);

        $response = $this->actingAs($user)
            ->post(route('consultas.store'), [
                'doctor_id' => $doctor->id,
                'type' => 'digital',
                'status' => 'saved',
                'consultation_date' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHasErrors('patient_id');
    });

    test('puede actualizar consulta no finalizada', function () {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
            'type' => 'digital',
            'doctor_id' => $doctor->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('consultas.update', $consultation->id), [
                'patient_id' => $consultation->patient_id,
                'doctor_id' => $consultation->doctor_id,
                'type' => 'manual',
                'status' => 'saved',
                'consultation_date' => now()->addDay()->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect(route('consultas.show', $consultation->id));

        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'status' => 'saved',
        ]);
    });

    test('no permite actualizar consulta finalizada', function () {
        $doctor = Doctor::factory()->create();
        $user = User::factory()->create(['doctor_id' => $doctor->id]);
        $consultation = Consultation::factory()->create([
            'status' => 'finalized',
            'doctor_id' => $doctor->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('consultas.update', $consultation->id), [
                'patient_id' => $consultation->patient_id,
                'doctor_id' => $consultation->doctor_id,
                'type' => 'manual',
                'status' => 'saved',
                'consultation_date' => now()->addDay()->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHasErrors('status');

        $consultation->refresh();
        expect($consultation->status->value())->toBe('finalized');
    });

    test('vista show integra secciones de signos vitales, nota soap y vacunas aplicadas', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['status' => 'draft']);
        $vaccine = Vaccine::factory()->create();

        VitalSign::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        SoapNote::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        Prescription::factory()->create([
            'consultation_id' => $consultation->id,
        ]);

        PatientVaccine::factory()->create([
            'consultation_id' => $consultation->id,
            'vaccine_id' => $vaccine->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('consultas.show', $consultation->id));

        $response->assertOk()
            ->assertSee('Signos Vitales')
            ->assertSee('Nota SOAP')
            ->assertSee('Receta')
            ->assertSee('Vacunas');
    });
});
