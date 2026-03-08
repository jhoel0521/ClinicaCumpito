<?php

namespace App\Contracts;

use App\DTOs\DoctorDTO;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Collection;

interface DoctorServiceContract
{
    /**
     * Obtener el perfil del doctor por ID de usuario
     */
    public function findByUserId(string $userId): ?Doctor;

    /**
     * Crear un nuevo perfil de doctor
     */
    public function create(DoctorDTO $dto): Doctor;

    /**
     * Actualizar perfil de doctor
     */
    public function update(string $id, DoctorDTO $dto): Doctor;

    /**
     * Obtener todos los doctores activos
     *
     * @return Collection<int, Doctor>
     */
    public function getActiveDoctors(): Collection;
}
