<?php

use App\DTOs\PatientVaccineDTO;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientVaccine;
use App\Models\User;
use App\Models\Vaccine;
use App\Services\PatientVaccineService;
use Livewire\Livewire;

test('el servicio de vacunas no permite registrar dos veces la misma dosis', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);
    $vaccine = Vaccine::factory()->create(['name' => 'Influenza']);

    $dto = PatientVaccineDTO::fromArray([
        'vaccine_id' => $vaccine->id,
        'applied_at' => '2026-08-07 09:00:00',
        'dose_number' => 1,
    ]);

    $service = new PatientVaccineService;
    $service->create($consultation->id, $dto);

    expect(fn () => $service->create($consultation->id, $dto))
        ->toThrow(DomainException::class, 'ya fue registrada');

    expect(PatientVaccine::count())->toBe(1);
});

test('el esquema de vacunación de la ficha diferencia aplicadas y pendientes', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    // 8 meses de edad: le corresponden vacunas de hasta 8 meses
    $patient = Patient::factory()->create(['date_of_birth' => '2025-12-07']);
    $vaccineAplicada = Vaccine::factory()->create([
        'name' => 'Influenza',
        'min_age_months' => 6,
        'dose_sequence' => 1,
        'recommended_age' => '6 meses',
    ]);
    $vaccinePendiente = Vaccine::factory()->create([
        'name' => 'SRP',
        'min_age_months' => 6,
        'dose_sequence' => 1,
        'recommended_age' => '6 meses',
    ]);

    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);
    PatientVaccine::factory()->create([
        'patient_id' => $patient->id,
        'consultation_id' => $consultation->id,
        'vaccine_id' => $vaccineAplicada->id,
        'applied_at' => '2026-07-10 09:00:00',
        'dose_number' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.show', $patient))
        ->assertOk()
        ->assertSeeText('Esquema de vacunación')
        ->assertSeeText('Influenza')
        ->assertSeeText('10/07/2026')
        ->assertSeeText('SRP')
        ->assertSeeText('Pendiente');
});

test('aplicar una vacuna ya registrada desde la consulta muestra error y no duplica', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
        'consultation_date' => '2026-08-07 09:00:00',
    ]);
    $vaccine = Vaccine::factory()->create([
        'name' => 'BCG',
        'min_age_months' => 0,
        'dose_sequence' => 1,
        'recommended_age' => 'Al nacer',
    ]);

    // Primera aplicación (vía service, con dose_number null como el flujo del componente)
    $dto = PatientVaccineDTO::fromArray([
        'vaccine_id' => $vaccine->id,
        'applied_at' => '2026-08-07 09:00:00',
    ]);
    (new PatientVaccineService)->create($consultation->id, $dto);

    // Segundo intento desde el componente de consulta
    Livewire::actingAs($user)
        ->test('consultation-vaccines', ['consultationId' => $consultation->id])
        ->call('applyVaccine', $vaccine->id)
        ->assertSee('ya fue registrada');

    expect(PatientVaccine::count())->toBe(1);
});
