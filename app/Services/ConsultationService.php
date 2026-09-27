<?php

namespace App\Services;

use App\Contracts\ConsultationServiceContract;
use App\DTOs\ConsultationDTO;
use App\Models\Consultation;
use App\Models\Patient;
use App\ValueObjects\ConsultationStatus;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ConsultationService implements ConsultationServiceContract
{
    public function create(ConsultationDTO $dto): Consultation
    {
        $this->ensureCoherentDate($dto);

        $consultation = Consultation::create($dto->toArray());

        return $this->freshConsultation($consultation);
    }

    public function update(string $id, ConsultationDTO $dto): Consultation
    {
        $consultation = Consultation::findOrFail($id);

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede editar una consulta finalizada.');
        }

        $this->ensureCoherentDate($dto);

        $consultation->update($dto->toArray());

        return $this->freshConsultation($consultation);
    }

    public function delete(string $id): bool
    {
        $consultation = Consultation::findOrFail($id);

        if ($this->statusValue($consultation) === ConsultationStatus::FINALIZED) {
            throw new \DomainException('No se puede eliminar una consulta finalizada.');
        }

        return (bool) $consultation->delete();
    }

    public function discardDraft(string $id): bool
    {
        $consultation = Consultation::findOrFail($id);

        if ($this->statusValue($consultation) !== ConsultationStatus::DRAFT) {
            throw new \DomainException('Solo se pueden descartar consultas en borrador.');
        }

        return (bool) $consultation->delete();
    }

    public function find(string $id): ?Consultation
    {
        return Consultation::with(['patient', 'doctor'])->find($id);
    }

    /**
     * @return LengthAwarePaginator<int, Consultation>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Consultation::query()
            ->with(['patient', 'doctor'])
            ->latest('consultation_date')
            ->paginate($perPage);
    }

    private function freshConsultation(Consultation $consultation): Consultation
    {
        $freshConsultation = $consultation->fresh(['patient', 'doctor']);

        if (! $freshConsultation instanceof Consultation) {
            throw new \RuntimeException('No se pudo refrescar la consulta.');
        }

        return $freshConsultation;
    }

    /**
     * Regla de dominio: la consulta no puede ser futura ni anterior al
     * nacimiento del paciente (rompe el cálculo de edad y las gráficas OMS).
     */
    private function ensureCoherentDate(ConsultationDTO $dto): void
    {
        $date = CarbonImmutable::parse($dto->consultation_date);

        if ($date->isAfter(now()->addMinutes(5))) {
            throw new \DomainException('La fecha de la consulta no puede ser futura.');
        }

        $birthDate = Patient::query()->whereKey($dto->patient_id)->value('date_of_birth');

        if ($birthDate !== null && $date->startOfDay()->isBefore(CarbonImmutable::parse($birthDate)->startOfDay())) {
            throw new \DomainException('La fecha de la consulta no puede ser anterior al nacimiento del paciente.');
        }
    }

    private function statusValue(Consultation $consultation): string
    {
        return $consultation->status instanceof ConsultationStatus
            ? $consultation->status->value()
            : (string) $consultation->status;
    }
}
