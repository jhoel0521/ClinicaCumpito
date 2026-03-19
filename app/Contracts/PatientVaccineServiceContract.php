<?php

namespace App\Contracts;

use App\DTOs\PatientVaccineDTO;
use App\Models\PatientVaccine;
use Illuminate\Support\Collection;

interface PatientVaccineServiceContract
{
    public function create(string $consultationId, PatientVaccineDTO $dto): PatientVaccine;

    public function update(string $patientVaccineId, PatientVaccineDTO $dto): PatientVaccine;

    /** @return Collection<int, PatientVaccine> */
    public function listByConsultation(string $consultationId): Collection;

    /** @return Collection<int, PatientVaccine> */
    public function listAllForPatient(string $patientId): Collection;

    public function delete(string $patientVaccineId): bool;
}
