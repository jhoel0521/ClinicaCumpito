<?php

namespace App\Services;

use App\Contracts\PatientVaccineServiceContract;
use App\DTOs\PatientVaccineDTO;
use App\Models\Consultation;
use App\Models\PatientVaccine;
use App\ValueObjects\ConsultationStatus;
use DomainException;
use Illuminate\Support\Collection;

class PatientVaccineService implements PatientVaccineServiceContract
{
    public function create(string $consultationId, PatientVaccineDTO $dto): PatientVaccine
    {
        $consultation = Consultation::findOrFail($consultationId);
        $this->ensureNotFinalized($consultation);

        $duplicateQuery = PatientVaccine::query()
            ->where('patient_id', $consultation->patient_id)
            ->where('vaccine_id', $dto->vaccine_id);

        if ($dto->dose_number !== null) {
            $duplicateQuery->where('dose_number', $dto->dose_number);
        } else {
            $duplicateQuery->whereNull('dose_number');
        }

        if ($duplicateQuery->exists()) {
            throw new DomainException('Esta dosis de la vacuna ya fue registrada para el paciente.');
        }

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
        $patientVaccine = PatientVaccine::with('consultation')->findOrFail($patientVaccineId);

        if ($patientVaccine->consultation instanceof Consultation) {
            $this->ensureNotFinalized($patientVaccine->consultation);
        }

        return (bool) $patientVaccine->delete();
    }

    private function ensureNotFinalized(Consultation $consultation): void
    {
        if ($consultation->status instanceof ConsultationStatus && $consultation->status->isFinalized()) {
            throw new DomainException('La consulta está finalizada: sus vacunas ya no se pueden modificar.');
        }
    }
}
