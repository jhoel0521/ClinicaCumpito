<?php

use App\DTOs\PacienteDTO;
use App\Models\MedicalCondition;
use App\Models\Patient;
use App\Services\PacienteService;
use Carbon\Carbon;

describe('PacienteService', function () {
    beforeEach(function () {
        $this->service = new PacienteService;
    });

    describe('create', function () {
        test('puede crear un nuevo paciente', function () {
            $user = \App\Models\User::factory()->create();
            $doctor = \App\Models\Doctor::factory()->create();

            $dto = new PacienteDTO(
                user_id: $user->id,
                responsible_doctor_id: $doctor->id,
                full_name: 'Juan Pérez García',
                date_of_birth: Carbon::parse('2020-01-15'),
                gender: 'M',
                birth_type: 'Normal',
                blood_group: 'O+',
                allergies: 'Sin alergias conocidas',
            );

            $patient = $this->service->create($dto);

            expect($patient)->toBeInstanceOf(Patient::class);
            expect($patient->full_name)->toBe('Juan Pérez García');
            expect($patient->date_of_birth->toDateString())->toBe('2020-01-15');
            expect($patient->gender->value())->toBe('M');
        })->group('paciente-service');

        test('puede crear un paciente con condiciones médicas', function () {
            $user = \App\Models\User::factory()->create();
            $condition = MedicalCondition::factory()->create(['name' => 'Chagas']);

            $dto = new PacienteDTO(
                user_id: $user->id,
                full_name: 'María López',
                date_of_birth: Carbon::parse('2019-06-20'),
                gender: 'F',
                medical_conditions: [
                    [
                        'condition_id' => $condition->id,
                        'status' => 'Positive',
                        'notes' => 'Diagnóstico confirmado',
                    ],
                ],
            );

            $patient = $this->service->create($dto);

            expect($patient->medicalConditions)->toHaveCount(1);
            expect($patient->medicalConditions->first()->name)->toBe('Chagas');
            expect($patient->medicalConditions->first()->pivot->status)->toBe('Positive');
        })->group('paciente-service');
    });

    describe('update', function () {
        test('puede actualizar un paciente existente', function () {
            $patient = Patient::factory()->create();
            $newName = 'Carlos Actualizado';

            $dto = new PacienteDTO(
                user_id: $patient->user_id,
                full_name: $newName,
                date_of_birth: Carbon::parse('2019-06-20'),
                gender: $patient->gender->value(),
                blood_group: $patient->blood_group->value(),
            );

            $updated = $this->service->update($patient->id, $dto);

            expect($updated->full_name)->toBe($newName);
        })->group('paciente-service');

        test('puede actualizar condiciones médicas de un paciente', function () {
            $patient = Patient::factory()->create();
            $condition1 = MedicalCondition::factory()->create(['name' => 'Chagas']);
            $condition2 = MedicalCondition::factory()->create(['name' => 'Syphilis']);

            $patient->medicalConditions()->attach($condition1->id, ['status' => 'Negative']);

            $dto = new PacienteDTO(
                user_id: $patient->user_id,
                full_name: $patient->full_name,
                date_of_birth: Carbon::instance($patient->date_of_birth),
                gender: $patient->gender->value(),
                medical_conditions: [
                    [
                        'condition_id' => $condition2->id,
                        'status' => 'Positive',
                    ],
                ],
            );

            $updated = $this->service->update($patient->id, $dto);

            expect($updated->medicalConditions)->toHaveCount(1);
            expect($updated->medicalConditions->first()->name)->toBe('Syphilis');
        })->group('paciente-service');
    });

    describe('delete', function () {
        test('puede eliminar un paciente', function () {
            $patient = Patient::factory()->create();
            $id = $patient->id;

            $deleted = $this->service->delete($id);

            expect($deleted)->toBeTrue();
            expect(Patient::find($id))->toBeNull();
        })->group('paciente-service');

        test('throws cuando paciente no existe', function () {
            $this->service->delete('invalid-id');
        })->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class)->group('paciente-service');
    });

    describe('find', function () {
        test('puede encontrar un paciente por ID', function () {
            $patient = Patient::factory()->create();

            $found = $this->service->find($patient->id);

            expect($found)->toBeInstanceOf(Patient::class);
            expect($found->id)->toBe($patient->id);
        })->group('paciente-service');

        test('retorna null cuando paciente no existe', function () {
            $found = $this->service->find('invalid-id');

            expect($found)->toBeNull();
        })->group('paciente-service');
    });

    describe('all', function () {
        test('puede obtener todos los pacientes', function () {
            Patient::factory()->count(3)->create();

            $patients = $this->service->all();

            expect($patients)->toHaveCount(3);
            expect($patients->first())->toBeInstanceOf(Patient::class);
        })->group('paciente-service');
    });

    describe('findByUserId', function () {
        test('puede encontrar un paciente por ID de usuario', function () {
            $user = \App\Models\User::factory()->create();
            $patient = Patient::factory()->create(['user_id' => $user->id]);

            $found = $this->service->findByUserId($patient->user_id);

            expect($found)->toBeInstanceOf(Patient::class);
            expect($found->user_id)->toBe($patient->user_id);
        })->group('paciente-service');

        test('retorna null cuando usuario no tiene paciente', function () {
            $found = $this->service->findByUserId('invalid-id');

            expect($found)->toBeNull();
        })->group('paciente-service');
    });
});
