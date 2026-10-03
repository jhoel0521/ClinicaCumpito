<?php

namespace App\Contracts;

use App\DTOs\ScheduledVisitDTO;
use App\Models\ScheduledVisit;
use App\ValueObjects\VisitStatus;
use Illuminate\Support\Collection;

interface ScheduledVisitServiceContract
{
    public function schedule(string $patientId, ScheduledVisitDTO $dto): ScheduledVisit;

    public function delete(string $scheduledVisitId): bool;

    /**
     * Visitas del paciente con su estado calculado, ordenadas por fecha.
     *
     * @return Collection<int, array{visit: ScheduledVisit, status: VisitStatus}>
     */
    public function listForPatient(string $patientId): Collection;

    /**
     * Cuántas visitas fueron a tiempo, en el mes, no vino y pendientes.
     *
     * @return array{on_time: int, late: int, missed: int, pending: int}
     */
    public function summaryForPatient(string $patientId): array;

    /**
     * Agenda del doctor entre dos fechas (Y-m-d, inclusive). Con $doctorId
     * null devuelve las visitas de todos los doctores.
     *
     * @return Collection<int, array{visit: ScheduledVisit, status: VisitStatus}>
     */
    public function agenda(?string $doctorId, string $from, string $to): Collection;

    /**
     * Visitas cuya fecha ya pasó (últimos $days días) y el paciente todavía
     * no vino: a quién hay que llamar.
     *
     * @return Collection<int, array{visit: ScheduledVisit, status: VisitStatus}>
     */
    public function overdue(?string $doctorId, int $days = 30): Collection;
}
