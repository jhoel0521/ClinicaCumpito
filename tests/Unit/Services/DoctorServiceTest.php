<?php

use App\DTOs\DoctorDTO;
use App\Models\Doctor;
use App\Models\User;
use App\Services\DoctorService;

describe('DoctorService', function () {
    beforeEach(function () {
        $this->service = new DoctorService;
    });

    test('puede crear un perfil de doctor', function () {
        $user = User::factory()->create();

        $dto = new DoctorDTO(
            user_id: $user->id,
            full_name: 'Dra. Ana Flores',
            specialty: 'Pediatría',
            license_number: 'MED-12345',
            active: true,
        );

        $doctor = $this->service->create($dto);

        expect($doctor)->toBeInstanceOf(Doctor::class);
        expect($doctor->user_id)->toBe($user->id);
        expect($doctor->full_name)->toBe('Dra. Ana Flores');
        expect($doctor->specialty)->toBe('Pediatría');
        expect($doctor->license_number->value())->toBe('MED-12345');
    })->group('doctor-service');

    test('puede actualizar un perfil de doctor', function () {
        $doctor = Doctor::factory()->create([
            'full_name' => 'Nombre Anterior',
            'specialty' => 'General',
            'license_number' => 'MED-11111',
        ]);

        $dto = new DoctorDTO(
            full_name: 'Nombre Actualizado',
            specialty: 'Cardiología',
            license_number: 'MED-22222',
            active: true,
        );

        $updated = $this->service->update($doctor->id, $dto);

        expect($updated->id)->toBe($doctor->id);
        expect($updated->full_name)->toBe('Nombre Actualizado');
        expect($updated->specialty)->toBe('Cardiología');
        expect($updated->license_number->value())->toBe('MED-22222');
    })->group('doctor-service');

    test('puede encontrar doctor por user_id', function () {
        $user = User::factory()->create();
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);

        $found = $this->service->findByUserId($user->id);

        expect($found)->toBeInstanceOf(Doctor::class);
        expect($found?->id)->toBe($doctor->id);
    })->group('doctor-service');

    test('retorna solo doctores activos', function () {
        Doctor::factory()->create(['active' => true]);
        Doctor::factory()->create(['active' => true]);
        Doctor::factory()->create(['active' => false]);

        $activeDoctors = $this->service->getActiveDoctors();

        expect($activeDoctors)->toHaveCount(2);
        expect($activeDoctors->every(fn (Doctor $doctor) => $doctor->active))->toBeTrue();
    })->group('doctor-service');
});
