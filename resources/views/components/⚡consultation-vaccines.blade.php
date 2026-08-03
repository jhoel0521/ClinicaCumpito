<?php

use App\Contracts\PatientVaccineServiceContract;
use App\DTOs\PatientVaccineDTO;
use App\Models\Consultation;
use App\Models\Vaccine;
use App\ValueObjects\ConsultationStatus;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component {
    public string $consultationId;

    public string $patientId = '';

    public string $doctorId = '';

    public ?int $ageInMonths = null;

    public bool $finalized = false;

    public string $errorMessage = '';

    /**
     * Keys: recommended_age label
     * Each value: { vaccines: list<array>, has_pending_due: bool, all_applied: bool, all_future: bool }
     *
     * @var array<string, array{vaccines: list<array<string, mixed>>, has_pending_due: bool, all_applied: bool, all_future: bool}>
     */
    public array $schedule = [];

    /**
     * Fecha de aplicación elegida por vacuna (vacuna_id → Y-m-d).
     * Permite cargar esquemas previos de pacientes nuevos con su fecha real.
     *
     * @var array<string, string>
     */
    public array $applyDates = [];

    /** Fecha de la consulta (Y-m-d): aplicaciones anteriores se marcan como "otro establecimiento". */
    public string $consultationDate = '';

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;
        $consultation = Consultation::with('patient')->findOrFail($consultationId);
        $this->patientId = $consultation->patient_id ?? '';
        $this->doctorId = $consultation->doctor_id ?? '';
        $this->consultationDate = $consultation->consultation_date?->format('Y-m-d') ?? now()->format('Y-m-d');

        if ($consultation->patient?->date_of_birth) {
            $this->ageInMonths = (int) Carbon::parse($consultation->patient->date_of_birth)->diffInMonths(now());
        }

        $this->finalized =
            $consultation->status instanceof ConsultationStatus
                ? $consultation->status->isFinalized()
                : (string) $consultation->status === ConsultationStatus::FINALIZED;

        $this->reload();
    }

    private function reload(): void
    {
        $applied = app(PatientVaccineServiceContract::class)
            ->listAllForPatient($this->patientId)
            ->keyBy('vaccine_id');

        $catalog = Vaccine::orderBy('min_age_months')
            ->orderBy('dose_sequence')
            ->get();

        $grouped = [];
        foreach ($catalog as $vaccine) {
            $ageLabel = $vaccine->recommended_age ?? 'Sin edad';
            $record = $applied->get($vaccine->id);
            $isDue =
                $this->ageInMonths !== null &&
                $vaccine->min_age_months !== null &&
                $this->ageInMonths >= $vaccine->min_age_months;

            if (! isset($grouped[$ageLabel])) {
                $grouped[$ageLabel] = [
                    'vaccines' => [],
                    'has_pending_due' => false,
                    'all_applied' => true,
                    'all_future' => true,
                ];
            }

            $grouped[$ageLabel]['vaccines'][] = [
                'vaccine_id' => $vaccine->id,
                'name' => $vaccine->name,
                'min_age_months' => $vaccine->min_age_months,
                'is_due' => $isDue,
                'applied' => $record
                    ? [
                        'id' => $record->id,
                        'applied_at' => $record->applied_at?->format('d/m/Y'),
                        'elsewhere' => $record->applied_elsewhere,
                        'this_consult' => $record->consultation_id === $this->consultationId,
                    ]
                    : null,
            ];

            if ($isDue && $record === null) {
                $grouped[$ageLabel]['has_pending_due'] = true;
            }
            if ($record === null) {
                $grouped[$ageLabel]['all_applied'] = false;
            }
            if ($isDue) {
                $grouped[$ageLabel]['all_future'] = false;
            }
        }
        $this->schedule = $grouped;
    }

    public function applyVaccine(string $vaccineId): void
    {
        if ($this->finalized) {
            return;
        }
        $this->errorMessage = '';

        // Fecha elegida por el usuario (por defecto hoy): permite cargar
        // el esquema previo de un paciente nuevo con las fechas reales.
        $rawDate = trim($this->applyDates[$vaccineId] ?? '');
        $appliedAt = $rawDate !== '' ? Carbon::parse($rawDate) : now();

        if ($appliedAt->isFuture()) {
            $this->errorMessage = 'La fecha de aplicación no puede ser futura.';

            return;
        }

        try {
            // Si la fecha es anterior a la consulta, la dosis se colocó en otro momento/lugar
            $appliedElsewhere = $appliedAt
                ->copy()
                ->startOfDay()
                ->lt(Carbon::parse($this->consultationDate)->startOfDay());

            $dto = new PatientVaccineDTO(
                vaccine_id: $vaccineId,
                applied_at: $appliedAt->toDateTimeString(),
                applied_by_doctor_id: $appliedElsewhere ? null : ($this->doctorId ?: null),
                application_site: null,
                dose_number: null,
                notes: $appliedElsewhere ? 'Registrada con fecha previa (esquema anterior)' : null,
                applied_elsewhere: $appliedElsewhere,
            );
            app(PatientVaccineServiceContract::class)->create($this->consultationId, $dto);
            unset($this->applyDates[$vaccineId]);
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al registrar vacuna: ' . $e->getMessage();
        }
    }

    public function removeApplied(string $patientVaccineId): void
    {
        if ($this->finalized) {
            return;
        }
        $this->errorMessage = '';
        try {
            app(PatientVaccineServiceContract::class)->delete($patientVaccineId);
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar: ' . $e->getMessage();
        }
    }
}; ?>

<section id="vacunas" dusk="section-vaccines" class="scroll-mt-16">
    <div
        class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center">
                    <svg
                        class="w-4 h-4 text-teal-600 dark:text-teal-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                        />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Esquema de Vacunas PAI Bolivia
                    </h2>

                    @if ($ageInMonths !== null)
                        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                            Paciente:
                            <span class="font-medium text-teal-600 dark:text-teal-400">
                                {{ $ageInMonths }} {{ $ageInMonths === 1 ? 'mes' : 'meses' }}
                            </span>
                        </p>
                    @else
                        <p class="text-xs text-amber-500 mt-0.5">Fecha de nacimiento no registrada</p>
                    @endif
                </div>
            </div>
            @if ($finalized)
                <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400"
                >
                    Finalizada
                </span>
            @endif
        </div>

        <div class="p-4 space-y-2">
            @if ($errorMessage)
                <div
                    class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300"
                >
                    {{ $errorMessage }}
                </div>
            @endif

            @if (empty($schedule))
                <p class="text-sm text-gray-400 dark:text-zinc-500 py-4 text-center">No hay vacunas en el catálogo.</p>
            @else
                @foreach ($schedule as $ageLabel => $group)
                    @php
                        $isPending = $group['has_pending_due'];
                        $isAllDone = $group['all_applied'];
                        $isFuture = $group['all_future'];
                        $defaultOpen = $isPending; // open if there are pending doses

                        if ($isPending) {
                            $groupBorder = 'border-amber-300 dark:border-amber-700/60';
                            $groupBg = 'bg-amber-50 dark:bg-amber-900/10';
                            $headerBg = 'bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30';
                            $badgeClass = 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300';
                            $badgeText = 'Dosis pendientes';
                        } elseif ($isAllDone) {
                            $groupBorder = 'border-emerald-200 dark:border-emerald-800/40';
                            $groupBg = 'bg-white dark:bg-zinc-900';
                            $headerBg = 'bg-emerald-50 dark:bg-emerald-900/10 hover:bg-emerald-50 dark:hover:bg-emerald-900/20';
                            $badgeClass = 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300';
                            $badgeText = 'Completo';
                        } else {
                            $groupBorder = 'border-gray-200 dark:border-zinc-700';
                            $groupBg = 'bg-white dark:bg-zinc-900';
                            $headerBg = 'bg-gray-50 dark:bg-zinc-800/40 hover:bg-gray-100 dark:hover:bg-zinc-800/60';
                            $badgeClass = 'bg-gray-100 dark:bg-zinc-700 text-gray-400 dark:text-zinc-400';
                            $badgeText = 'Aún no corresponde';
                        }
                    @endphp

                    <div
                        x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }"
                        class="border {{ $groupBorder }} rounded-xl overflow-hidden"
                    >
                        {{-- Group header (clickable) --}}
                        <button
                            type="button"
                            @click="open = !open"
                            class="w-full flex items-center justify-between px-4 py-2.5 {{ $headerBg }} transition-colors text-left"
                        >
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $ageLabel }}</span>
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}"
                                >
                                    {{ $badgeText }}
                                </span>
                                <svg
                                    :class="open ? 'rotate-180' : ''"
                                    class="w-4 h-4 text-gray-400 dark:text-zinc-500 transition-transform duration-200"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </div>
                        </button>

                        {{-- Vaccine rows --}}
                        <div x-show="open" x-transition class="{{ $groupBg }}">
                            @foreach ($group['vaccines'] as $vax)
                                @php
                                    $isApplied = $vax['applied'] !== null;
                                    $isElsewhere = $isApplied && $vax['applied']['elsewhere'];
                                    $isAppliedHere = $isApplied && ! $isElsewhere;
                                    $isDue = $vax['is_due'];
                                @endphp

                                <div
                                    class="flex items-center gap-3 px-4 py-2.5 border-t border-gray-100 dark:border-zinc-800"
                                    dusk="vaccine-row"
                                >
                                    {{-- Status icon --}}
                                    <div class="flex-shrink-0 w-5 text-center">
                                        @if ($isAppliedHere)
                                            <svg
                                                class="w-4 h-4 text-emerald-500"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2.5"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        @elseif ($isElsewhere)
                                            <svg
                                                class="w-4 h-4 text-blue-400 dark:text-blue-500"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4"
                                                />
                                            </svg>
                                        @elseif ($isDue)
                                            <svg
                                                class="w-4 h-4 text-amber-500"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                                                />
                                            </svg>
                                        @else
                                            <svg
                                                class="w-4 h-4 text-gray-300 dark:text-zinc-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                                />
                                            </svg>
                                        @endif
                                    </div>

                                    {{-- Vaccine name --}}
                                    <span
                                        class="flex-1 text-sm {{ $isApplied ? 'text-gray-700 dark:text-gray-300' : ($isDue ? 'text-gray-900 dark:text-gray-100 font-medium' : 'text-gray-400 dark:text-zinc-500') }}"
                                    >
                                        {{ $vax['name'] }}
                                    </span>

                                    {{-- Applied info / action buttons --}}
                                    @if ($isApplied)
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-xs {{ $isAppliedHere ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-500 dark:text-blue-400' }}"
                                            >
                                                {{ $vax['applied']['applied_at'] }}
                                                @if ($isElsewhere)
                                                    <span class="italic">· otro establecimiento</span>
                                                @endif
                                            </span>
                                            @if (! $finalized && $vax['applied']['this_consult'])
                                                <button
                                                    wire:click="removeApplied('{{ $vax['applied']['id'] }}')"
                                                    wire:loading.attr="disabled"
                                                    title="Marcar como NO aplicada"
                                                    dusk="vaccine-no-btn"
                                                    class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium border border-gray-300 dark:border-zinc-600 text-gray-400 dark:text-zinc-500 hover:border-red-400 hover:text-red-500 dark:hover:text-red-400 transition"
                                                >
                                                    No
                                                </button>
                                            @endif
                                        </div>
                                    @elseif (! $finalized && $isDue)
                                        <div class="flex items-center gap-1.5">
                                            <input
                                                type="date"
                                                wire:model="applyDates.{{ $vax['vaccine_id'] }}"
                                                max="{{ now()->format('Y-m-d') }}"
                                                title="Fecha de aplicación (déjala vacía si es hoy)"
                                                dusk="vaccine-date-{{ $vax['vaccine_id'] }}"
                                                class="w-[7.5rem] px-1.5 py-1 text-xs border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-teal-500 focus:border-teal-500"
                                            />
                                            <button
                                                wire:click="applyVaccine('{{ $vax['vaccine_id'] }}')"
                                                wire:loading.attr="disabled"
                                                dusk="vaccine-si-btn"
                                                title="Sí, tiene esta vacuna (usa la fecha indicada o hoy)"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-600 hover:bg-teal-700 text-white transition disabled:opacity-50"
                                            >
                                                Sí
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>
