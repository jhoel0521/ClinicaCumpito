<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;

describe('ConsultationController', function () {
    test('usuario autenticado puede ver lista de consultas', function () {
        Consultation::factory()->count(3)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('consultas.index'));

        $response->assertStatus(200)
            ->assertViewIs('consultas.index')
            ->assertViewHas('consultations');
    });

    test('usuario no autenticado es redirigido a login', function () {
        $response = $this->get(route('consultas.index'));

        $response->assertRedirect(route('login'));
    });

    test('puede crear una consulta con datos validos', function () {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

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
            'status' => 'saved',
        ]);
    });

    test('falla validacion al crear consulta sin paciente', function () {
        $user = User::factory()->create();
        $doctor = Doctor::factory()->create();

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
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'status' => 'saved',
            'type' => 'digital',
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
            'type' => 'manual',
        ]);
    });

    test('no permite actualizar consulta finalizada', function () {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'status' => 'finalized',
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
});
