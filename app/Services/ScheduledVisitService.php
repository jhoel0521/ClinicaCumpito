<?php

namespace App\Services;

use App\Contracts\ScheduledVisitServiceContract;
use App\DTOs\ScheduledVisitDTO;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\ScheduledVisit;
use App\ValueObjects\VisitStatus;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ScheduledVisitService implements ScheduledVisitServiceContract
{
    public function schedule(string $patientId, ScheduledVisitDTO $dto): ScheduledVisit
    {
        $patient = Patient::findOrFail($patientId);

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $dto->scheduled_for);
        } catch (\Throwable) {
            $date = null;
        }

        // El formato estricto descarta fechas que Carbon "corrige" (31/02 → 03/03).
        if ($date === null || $date->format('Y-m-d') !== $dto->scheduled_for) {
            throw new DomainException('La fecha de la visita no es válida.');
        }

        if ($date->lt(CarbonImmutable::today())) {
            throw new DomainException('No se puede programar una visita en una fecha pasada.');
        }

        if ($dto->reason === '' || mb_strlen($dto->reason) > 255) {
            throw new DomainException('Indica el motivo de la visita (máximo 255 caracteres).');
        }

        $user = Auth::user();

        return ScheduledVisit::create([
            'patient_id' => $patient->id,
            // El doctor que programa; si el usuario no es doctor, el responsable del paciente.
            'doctor_id' => $user->doctor_id ?? $patient->responsible_doctor_id,
            'created_by_user_id' => $user?->id,
            ...$dto->toArray(),
        ]);
    }

    public function delete(string $scheduledVisitId): bool
    {
        $visit = ScheduledVisit::findOrFail($scheduledVisitId);

        $status = VisitStatus::evaluate($visit->scheduled_for, $this->consultationDates($visit->patient_id), CarbonImmutable::today());

        // Las cumplidas o vencidas son historial del paciente: no se borran.
        if (! $status->isPending()) {
            throw new DomainException('Solo se pueden quitar visitas pendientes; las demás forman parte del historial.');
        }

        return (bool) $visit->delete();
    }

    public function listForPatient(string $patientId): Collection
    {
        $dates = $this->consultationDates($patientId);
        $today = CarbonImmutable::today();

        return ScheduledVisit::query()
            ->where('patient_id', $patientId)
            ->orderBy('scheduled_for')
            ->orderBy('created_at')
            ->get()
            ->map(fn (ScheduledVisit $visit) => [
                'visit' => $visit,
                'status' => VisitStatus::evaluate($visit->scheduled_for, $dates, $today),
            ])
            ->values();
    }

    public function summaryForPatient(string $patientId): array
    {
        return VisitStatus::summarize($this->listForPatient($patientId)->pluck('status'));
    }

    /**
     * Cualquier consulta cuenta como asistencia (borrador, guardada o finalizada):
     * el paciente vino.
     *
     * @return Collection<int, CarbonImmutable>
     */
    private function consultationDates(string $patientId): Collection
    {
        return Consultation::query()
            ->where('patient_id', $patientId)
            ->pluck('consultation_date')
            ->map(fn ($date) => CarbonImmutable::parse($date))
            ->values();
    }
}
