<?php

namespace App\Services;

use App\Contracts\PatientVaccineServiceContract;
use App\DTOs\PatientVaccineDTO;
use App\Models\PatientVaccine;
use Illuminate\Support\Collection;

class PatientVaccineService implements PatientVaccineServiceContract
{
    public function create(string $consultationId, PatientVaccineDTO $dto): PatientVaccine
    {
        $patientVaccine = PatientVaccine::create([
            'consultation_id' => $consultationId,
            ...$dto->toArray(),
        ]);

        $freshPatientVaccine = $patientVaccine->fresh(['vaccine']);
        if (! $freshPatientVaccine instanceof PatientVaccine) {
            throw new \RuntimeException('No se pudo refrescar la aplicación de vacuna.');
        }

        return $freshPatientVaccine;
    }

    public function update(string $patientVaccineId, PatientVaccineDTO $dto): PatientVaccine
    {
        $patientVaccine = PatientVaccine::findOrFail($patientVaccineId);
        $patientVaccine->update($dto->toArray());

        $freshPatientVaccine = $patientVaccine->fresh(['vaccine']);
        if (! $freshPatientVaccine instanceof PatientVaccine) {
            throw new \RuntimeException('No se pudo refrescar la aplicación de vacuna.');
        }

        return $freshPatientVaccine;
    }

    public function listByConsultation(string $consultationId): Collection
    {
        return PatientVaccine::query()
            ->where('consultation_id', $consultationId)
            ->with('vaccine')
            ->orderByDesc('applied_at')
            ->get();
    }

    public function delete(string $patientVaccineId): bool
    {
        $patientVaccine = PatientVaccine::findOrFail($patientVaccineId);

        return (bool) $patientVaccine->delete();
    }
}
