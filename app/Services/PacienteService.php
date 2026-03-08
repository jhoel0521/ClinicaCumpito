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

        $freshPatient = $patient->fresh();
        if (! $freshPatient instanceof Patient) {
            throw new \RuntimeException('Failed to fetch fresh patient model');
        }

        return $freshPatient;

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

        $freshPatient = $patient->fresh();
        if (! $freshPatient instanceof Patient) {
            throw new \RuntimeException('Failed to fetch fresh patient model');
        }

        return $freshPatient;

    }

    /**
     * Eliminar un paciente
     */
    public function delete(string $id): bool
    {
        if (! \Illuminate\Support\Str::isUuid($id)) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel(Patient::class, [$id]);
        }

        $patient = Patient::findOrFail($id);

        return (bool) $patient->delete();
    }

    /**
     * Encontrar un paciente por ID
     */
    public function find(string $id): ?Patient
    {
        if (! \Illuminate\Support\Str::isUuid($id)) {
            return null;
        }

        return Patient::find($id);
    }

    /**
     * Obtener todos los pacientes
     *
     * @return Collection<int, Patient>
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
        if (! \Illuminate\Support\Str::isUuid($userId)) {
            return null;
        }

        return Patient::where('user_id', $userId)->first();
    }

    /**
     * Sincronizar condiciones médicas del paciente
     *
     * @param  array<int, array{condition_id: string, status?: string, notes?: string}>  $conditions
     */
    private function syncMedicalConditions(Patient $patient, array $conditions): void
    {
        $syncData = [];
        foreach ($conditions as $condition) {
            if (empty($condition['status'])) {
                continue; // "No evaluado" — no se sincroniza
            }
            $syncData[$condition['condition_id']] = [
                'status' => $condition['status'],
                'notes' => $condition['notes'] ?? null,
            ];
        }
        $patient->medicalConditions()->sync($syncData);
    }
}
