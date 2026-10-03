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
}
