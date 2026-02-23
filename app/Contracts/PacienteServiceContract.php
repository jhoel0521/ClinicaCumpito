<?php

namespace App\Contracts;

use App\DTOs\PacienteDTO;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;

interface PacienteServiceContract
{
    /**
     * Crear un nuevo paciente
     */
    public function create(PacienteDTO $dto): Patient;

    /**
     * Actualizar un paciente existente
     */
    public function update(string $id, PacienteDTO $dto): Patient;

    /**
     * Eliminar un paciente
     */
    public function delete(string $id): bool;

    /**
     * Encontrar un paciente por ID
     */
    public function find(string $id): ?Patient;

    /**
     * Obtener todos los pacientes
     */
    public function all(): Collection;

    /**
     * Encontrar pacientes por ID de usuario
     */
    public function findByUserId(string $userId): ?Patient;
}
