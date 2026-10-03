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

        $status = VisitStatus::evaluate($visit->scheduled_for, $this->consultationDates($visit->patient_id), CarbonImmutable::today(), $visit->created_at);

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
                'status' => VisitStatus::evaluate($visit->scheduled_for, $dates, $today, $visit->created_at),
            ])
            ->values();
    }

    public function summaryForPatient(string $patientId): array
    {
        return VisitStatus::summarize($this->listForPatient($patientId)->pluck('status'));
    }

    public function agenda(?string $doctorId, string $from, string $to): Collection
    {
        return $this->withStatus(
            $this->doctorVisits($doctorId)
                ->whereBetween('scheduled_for', [$from, $to])
                ->get(),
        );
    }

    public function overdue(?string $doctorId, int $days = VisitStatus::LATE_DAYS_AFTER): Collection
    {
        $today = CarbonImmutable::today();

        return $this->withStatus(
            $this->doctorVisits($doctorId)
                ->whereBetween('scheduled_for', [$today->subDays($days)->format('Y-m-d'), $today->subDay()->format('Y-m-d')])
                ->get(),
        )
            // Ya pasó la fecha y todavía no hay consulta: hay que llamarlo.
            ->filter(fn (array $row) => $row['status']->isPending())
            ->values();
    }

    /** @return \Illuminate\Database\Eloquent\Builder<ScheduledVisit> */
    private function doctorVisits(?string $doctorId): \Illuminate\Database\Eloquent\Builder
    {
        return ScheduledVisit::query()
            ->with('patient.user')
            ->when($doctorId !== null, fn ($query) => $query->where('doctor_id', $doctorId))
            ->orderBy('scheduled_for')
            ->orderBy('created_at');
    }

    /**
     * Estado de varias visitas con una sola consulta a la base.
     *
     * @param  \Illuminate\Support\Collection<int, ScheduledVisit>  $visits
     * @return Collection<int, array{visit: ScheduledVisit, status: VisitStatus}>
     */
    private function withStatus(Collection $visits): Collection
    {
        $datesByPatient = Consultation::query()
            ->whereIn('patient_id', $visits->pluck('patient_id')->unique()->values())
            ->get(['patient_id', 'consultation_date'])
            ->groupBy('patient_id')
            ->map(fn ($rows) => $rows->map(fn ($c) => CarbonImmutable::parse($c->consultation_date))->values());
        $today = CarbonImmutable::today();

        return $visits
            ->map(fn (ScheduledVisit $visit) => [
                'visit' => $visit,
                'status' => VisitStatus::evaluate($visit->scheduled_for, $datesByPatient->get($visit->patient_id, collect()), $today, $visit->created_at),
            ])
            ->values();
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
