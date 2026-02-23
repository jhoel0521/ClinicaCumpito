<?php

namespace App\Services;

use App\Contracts\PacienteServiceContract;
use App\DTOs\PacienteDTO;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;

class PacienteService implements PacienteServiceContract
{
    /**
     * Crear un nuevo paciente
     */
    public function create(PacienteDTO $dto): Patient
    {
        $patient = Patient::create($dto->toArray());

        if ($dto->medical_conditions) {
            $this->syncMedicalConditions($patient, $dto->medical_conditions);
        }

        return $patient->fresh();
    }

    /**
     * Actualizar un paciente existente
     */
    public function update(string $id, PacienteDTO $dto): Patient
    {
        $patient = Patient::findOrFail($id);
        $patient->update($dto->toArray());

        if ($dto->medical_conditions) {
            $this->syncMedicalConditions($patient, $dto->medical_conditions);
        }

        return $patient->fresh();
    }

    /**
     * Eliminar un paciente
     */
    public function delete(string $id): bool
    {
        $patient = Patient::findOrFail($id);

        return $patient->delete();
    }

    /**
     * Encontrar un paciente por ID
     */
    public function find(string $id): ?Patient
    {
        return Patient::find($id);
    }

    /**
     * Obtener todos los pacientes
     */
    public function all(): Collection
    {
        return Patient::all();
    }

    /**
     * Encontrar pacientes por ID de usuario
     */
    public function findByUserId(string $userId): ?Patient
    {
        return Patient::where('user_id', $userId)->first();
    }

    /**
     * Sincronizar condiciones médicas del paciente
     */
    private function syncMedicalConditions(Patient $patient, array $conditions): void
    {
        $syncData = [];
        foreach ($conditions as $condition) {
            $syncData[$condition['condition_id']] = [
                'status' => $condition['status'] ?? 'Not tested',
                'notes' => $condition['notes'] ?? null,
            ];
        }
        $patient->medicalConditions()->sync($syncData);
    }
}
