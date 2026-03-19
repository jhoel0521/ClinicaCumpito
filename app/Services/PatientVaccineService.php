<?php

namespace App\Services;

use App\Contracts\PatientVaccineServiceContract;
use App\DTOs\PatientVaccineDTO;
use App\Models\Consultation;
use App\Models\PatientVaccine;
use Illuminate\Support\Collection;

class PatientVaccineService implements PatientVaccineServiceContract
{
    public function create(string $consultationId, PatientVaccineDTO $dto): PatientVaccine
    {
        $consultation = Consultation::findOrFail($consultationId);

        $patientVaccine = PatientVaccine::create([
            'patient_id' => $consultation->patient_id,
            'consultation_id' => $consultationId,
            'applied_by_doctor_id' => $dto->applied_by_doctor_id ?? $consultation->doctor_id,
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

    public function listAllForPatient(string $patientId): Collection
    {
        return PatientVaccine::with('vaccine')
            ->where('patient_id', $patientId)
            ->orderBy('applied_at')
            ->get();
    }

    public function delete(string $patientVaccineId): bool
    {
        $patientVaccine = PatientVaccine::findOrFail($patientVaccineId);

        return (bool) $patientVaccine->delete();
    }
}
