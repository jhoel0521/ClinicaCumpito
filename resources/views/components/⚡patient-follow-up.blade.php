<?php

use App\Contracts\ScheduledVisitServiceContract;
use App\DTOs\ScheduledVisitDTO;
use App\Models\Consultation;
use App\Models\Patient;
use App\ValueObjects\VisitStatus;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Seguimiento del paciente en una sola fila:
 * - Izquierda: controles mensuales 0–12 meses + cumplimiento de visitas.
 * - Derecha: calendario compacto (consultas y visitas programadas) +
 *   próximas visitas activas + programar visita.
 *
 * Es un solo componente para que ambos lados se actualicen juntos.
 * "Control extra" y "visita programada" son lo mismo (ScheduledVisit).
 */
new class extends Component {
    public const CONTROL_MONTHS = 12;

    public const UPCOMING_LIMIT = 3;

    public Patient $patient;

    public string $month = '';

    public bool $showScheduleForm = false;

    public string $newDate = '';

    public string $newReason = '';

    public string $errorMessage = '';

    public function mount(Patient $patient): void
    {
        abort_unless(auth()->user()?->can('view', $patient), 403);

        $this->patient = $patient;
        $this->month = now()->format('Y-m');
    }

    public function prevMonth(): void
    {
        $this->month = Carbon::parse($this->month . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = Carbon::parse($this->month . '-01')->addMonth()->format('Y-m');
    }

    /** Abre "Programar visita"; desde el calendario llega con el día elegido. */
    public function openSchedule(?string $date = null): void
    {
        abort_unless(auth()->user()?->can('update', $this->patient), 403);

        $this->errorMessage = '';
        $this->newDate = $date !== null && $date >= now()->format('Y-m-d') ? $date : '';
        $this->showScheduleForm = true;
    }

    public function cancelSchedule(): void
    {
        $this->showScheduleForm = false;
        $this->newDate = '';
        $this->newReason = '';
        $this->errorMessage = '';
    }

    public function scheduleVisit(): void
    {
        abort_unless(auth()->user()?->can('update', $this->patient), 403);

        $this->errorMessage = '';

        try {
            app(ScheduledVisitServiceContract::class)->schedule(
                $this->patient->id,
                ScheduledVisitDTO::fromArray(['scheduled_for' => $this->newDate, 'reason' => $this->newReason]),
            );

            $this->month = Carbon::parse($this->newDate)->format('Y-m');
            $this->cancelSchedule();
            $this->dispatch('notify', type: 'success', message: 'Visita programada.');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function deleteVisit(string $visitId): void
    {
        abort_unless(auth()->user()?->can('update', $this->patient), 403);

        try {
            // Solo visitas de este paciente.
            $this->patient->scheduledVisits()->findOrFail($visitId);
            app(ScheduledVisitServiceContract::class)->delete($visitId);
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    /**
     * Controles mensuales 0–12: cada mes queda asociado a la consulta más
     * reciente de ese mes de edad. No crea consultas.
     *
     * @return array<int, array{month: int, consultation: ?Consultation, future: bool}>
     */
    public function controls(): array
    {
        if ($this->patient->date_of_birth === null) {
            return [];
        }

        $byMonth = [];
        $consultations = Consultation::query()
            ->where('patient_id', $this->patient->id)
            ->whereIn('status', ['saved', 'finalized'])
            ->orderByDesc('consultation_date')
            ->get();

        foreach ($consultations as $consultation) {
            $month = $this->patient->ageAt($consultation->consultation_date)?->months();
            if ($month !== null && $month <= self::CONTROL_MONTHS) {
                $byMonth[$month] ??= $consultation;
            }
        }

        $currentMonth = $this->patient->ageAt(now())?->months() ?? 0;
        $controls = [];
        for ($month = 0; $month <= self::CONTROL_MONTHS; $month++) {
            $controls[] = [
                'month' => $month,
                'consultation' => $byMonth[$month] ?? null,
                'future' => $month > $currentMonth,
            ];
        }

        return $controls;
    }

    public function with(): array
    {
        $monthStart = Carbon::parse($this->month . '-01')->startOfMonth();

        $consultationsByDay = Consultation::query()
            ->where('patient_id', $this->patient->id)
            ->whereIn('status', ['saved', 'finalized'])
            ->whereYear('consultation_date', $monthStart->year)
            ->whereMonth('consultation_date', $monthStart->month)
            ->orderBy('consultation_date')
            ->get()
            ->keyBy(fn ($c) => $c->consultation_date->format('Y-m-d'));

        $visits = app(ScheduledVisitServiceContract::class)->listForPatient($this->patient->id);

        $summary = VisitStatus::summarize($visits->pluck('status'));

        $pending = $visits->filter(fn ($row) => $row['status']->isPending())->values();

        return [
            'monthStart' => $monthStart,
            'monthLabel' => $monthStart->isoFormat('MMMM YYYY'),
            'consultationsByDay' => $consultationsByDay,
            'visitsByDay' => $visits->groupBy(fn ($row) => $row['visit']->scheduled_for->format('Y-m-d')),
            'upcoming' => $pending->take(self::UPCOMING_LIMIT),
            'moreUpcoming' => max(0, $pending->count() - self::UPCOMING_LIMIT),
            'summary' => $summary,
            'today' => now()->format('Y-m-d'),
            'canEdit' => auth()->user()?->can('update', $this->patient) ?? false,
        ];
    }
}; ?>

@php
    $statusDot = [
        VisitStatus::PENDING => 'bg-amber-400',
        VisitStatus::ON_TIME => 'bg-emerald-500',
        VisitStatus::LATE => 'bg-sky-500',
        VisitStatus::MISSED => 'bg-red-500',
    ];
    $controls = $this->controls();
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4" dusk="patient-follow-up">
    {{-- ── Izquierda: controles 0–12 meses + cumplimiento ── --}}
    <div class="space-y-4">
        @if ($controls !== [])
            @php
                $completed = collect($controls)->filter(fn ($c) => $c['consultation'] !== null)->count();
            @endphp

            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">Controles mensuales 0–12 meses</h3>
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $completed }} de 13 controles</span>
                </div>
                <div class="grid grid-cols-7 gap-1.5" dusk="monthly-controls">
                    @foreach ($controls as $control)
                        @php
                            $label = $control['month'] === 0 ? 'RN' : $control['month'] . 'm';
                            $title = $control['month'] === 0 ? 'Recién nacido' : 'Mes ' . $control['month'];
                        @endphp

                        @if ($control['consultation'])
                            <a
                                href="{{ route('consultas.show', $control['consultation']->id) }}"
                                class="flex flex-col items-center gap-0.5 rounded-lg border border-teal-200 dark:border-teal-800 bg-teal-50 dark:bg-teal-900/20 py-1.5 text-center transition hover:border-teal-400"
                                title="{{ $title }} — {{ $control['consultation']->consultation_date->format('d/m/Y') }}"
                                dusk="control-month-{{ $control['month'] }}"
                            >
                                <span class="text-[10px] font-semibold text-teal-700 dark:text-teal-300">{{ $label }}</span>
                                <flux:icon.check-circle class="size-4 text-teal-500" />
                            </a>
                        @elseif ($control['future'])
                            <div
                                class="flex flex-col items-center gap-0.5 rounded-lg border border-zinc-100 dark:border-zinc-800 py-1.5 text-center"
                                title="{{ $title }} — todavía no cumple esta edad"
                                dusk="control-future-{{ $control['month'] }}"
                            >
                                <span class="text-[10px] font-semibold text-zinc-300 dark:text-zinc-600">{{ $label }}</span>
                                <span class="size-4 text-zinc-300 dark:text-zinc-600 text-xs leading-4">·</span>
                            </div>
                        @else
                            <div
                                class="flex flex-col items-center gap-0.5 rounded-lg border border-dashed border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 py-1.5 text-center"
                                title="{{ $title }} — sin control"
                                dusk="control-missing-{{ $control['month'] }}"
                            >
                                <span class="text-[10px] font-semibold text-zinc-400 dark:text-zinc-500">{{ $label }}</span>
                                <flux:icon.x-mark class="size-4 text-zinc-300 dark:text-zinc-600" />
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Cumplimiento de visitas programadas --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4" dusk="visit-summary">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">Cumplimiento de visitas</h3>
                <a
                    href="{{ route('pacientes.visitas', $patient) }}"
                    wire:navigate
                    class="text-xs font-medium text-teal-600 dark:text-teal-400 hover:underline"
                >
                    Ver todas →
                </a>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 py-2">
                    <p class="text-lg font-bold text-emerald-700 dark:text-emerald-300" dusk="summary-on-time">{{ $summary['on_time'] }}</p>
                    <p class="text-[11px] text-emerald-700/80 dark:text-emerald-300/80">A tiempo</p>
                </div>
                <div class="rounded-lg bg-sky-50 dark:bg-sky-900/20 py-2">
                    <p class="text-lg font-bold text-sky-700 dark:text-sky-300" dusk="summary-late">{{ $summary['late'] }}</p>
                    <p class="text-[11px] text-sky-700/80 dark:text-sky-300/80">En el mes</p>
                </div>
                <div class="rounded-lg bg-red-50 dark:bg-red-900/20 py-2">
                    <p class="text-lg font-bold text-red-700 dark:text-red-300" dusk="summary-missed">{{ $summary['missed'] }}</p>
                    <p class="text-[11px] text-red-700/80 dark:text-red-300/80">No vino</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Derecha: calendario compacto + próximas visitas ── --}}
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">Calendario de consultas</h3>
            <div class="flex items-center gap-1">
                <button
                    wire:click="prevMonth"
                    class="p-1 rounded-md border border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                    title="Mes anterior"
                >
                    <flux:icon.chevron-left class="size-3.5" />
                </button>
                <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 capitalize min-w-24 text-center">
                    {{ $monthLabel }}
                </span>
                <button
                    wire:click="nextMonth"
                    class="p-1 rounded-md border border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                    title="Mes siguiente"
                >
                    <flux:icon.chevron-right class="size-3.5" />
                </button>
            </div>
        </div>

        <div class="grid grid-cols-7 text-center text-[10px] font-semibold uppercase text-zinc-400 dark:text-zinc-500 mb-0.5">
            <span>L</span><span>M</span><span>M</span><span>J</span><span>V</span><span>S</span><span>D</span>
        </div>

        <div class="grid grid-cols-7 gap-0.5" dusk="consultation-calendar">
            @php
                $firstDay = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
                $weeks = (int) ceil(($firstDay->diffInDays($monthStart->copy()->endOfMonth()) + 1) / 7);
            @endphp

            @for ($i = 0; $i < $weeks * 7; $i++)
                @php
                    $date = $firstDay->copy()->addDays($i);
                    $dateKey = $date->format('Y-m-d');
                    $inMonth = $date->month === $monthStart->month;
                    $consultation = $consultationsByDay->get($dateKey);
                    $dayVisits = $visitsByDay->get($dateKey, collect());
                    $isToday = $dateKey === $today;
                    $canSchedule = $canEdit && $dateKey >= $today;
                @endphp

                @if (! $inMonth)
                    <div class="h-8"></div>
                @else
                    <div class="relative h-8">
                        @if ($consultation)
                            <a
                                href="{{ route('consultas.show', $consultation->id) }}"
                                @class([
                                    'flex items-center justify-center h-8 rounded-md text-xs font-bold transition hover:brightness-95',
                                    'bg-teal-500 text-white' => $dateKey >= now()->subDays(30)->format('Y-m-d'),
                                    'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300' => $dateKey < now()->subDays(30)->format('Y-m-d'),
                                ])
                                title="{{ $date->format('d/m/Y') }} — Consulta"
                                dusk="calendar-consultation-{{ $date->format('d') }}"
                            >
                                {{ $date->day }}
                            </a>
                        @elseif ($canSchedule)
                            <button
                                type="button"
                                wire:click="openSchedule('{{ $dateKey }}')"
                                @class([
                                    'flex items-center justify-center w-full h-8 rounded-md text-xs transition hover:bg-teal-50 dark:hover:bg-teal-900/20',
                                    'ring-1 ring-inset ring-teal-500/40 font-bold text-teal-700 dark:text-teal-300' => $isToday,
                                    'text-zinc-500 dark:text-zinc-400' => ! $isToday,
                                ])
                                title="Programar visita el {{ $date->format('d/m/Y') }}"
                            >
                                {{ $date->day }}
                            </button>
                        @else
                            <div class="flex items-center justify-center h-8 text-xs text-zinc-400 dark:text-zinc-600">
                                {{ $date->day }}
                            </div>
                        @endif

                        {{-- Visitas programadas ese día: un punto por visita, color según cumplimiento --}}
                        @if ($dayVisits->isNotEmpty())
                            <span
                                class="pointer-events-none absolute bottom-0.5 left-1/2 -translate-x-1/2 flex gap-0.5"
                                dusk="calendar-visit-{{ $date->format('d') }}"
                                title="{{ $dayVisits->map(fn ($row) => $row['visit']->reason . ' (' . $row['status']->label() . ')')->implode(', ') }}"
                            >
                                @foreach ($dayVisits->take(3) as $row)
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDot[$row['status']->value()] }} ring-1 ring-white dark:ring-zinc-900"></span>
                                @endforeach
                            </span>
                        @endif
                    </div>
                @endif
            @endfor
        </div>

        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] text-zinc-500 dark:text-zinc-400">
            <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-teal-500"></span>Consulta</span>
            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span>Programada</span>
            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>A tiempo</span>
            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-sky-500"></span>En el mes</span>
            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span>No vino</span>
        </div>

        {{-- Próximas visitas activas (máximo 3; el resto en "Ver todas") --}}
        <div class="mt-3 border-t border-zinc-100 dark:border-zinc-800 pt-3" dusk="upcoming-visits">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-1.5">
                Próximas visitas
            </p>
            @forelse ($upcoming as $row)
                <div wire:key="upcoming-{{ $row['visit']->id }}" class="flex items-center justify-between gap-2 py-1 text-sm">
                    <span class="min-w-0 truncate">
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $row['visit']->scheduled_for->format('d/m/Y') }}</span>
                        <span class="text-zinc-500 dark:text-zinc-400">· {{ $row['visit']->reason }}</span>
                    </span>
                    @if ($canEdit)
                        <button
                            type="button"
                            wire:click="deleteVisit('{{ $row['visit']->id }}')"
                            data-swal-confirm="¿Quitar esta visita programada?"
                            class="shrink-0 text-zinc-400 hover:text-red-500 transition"
                            title="Quitar visita"
                        >
                            <flux:icon.trash class="size-4" />
                        </button>
                    @endif
                </div>
            @empty
                <p class="text-xs text-zinc-400 dark:text-zinc-500 italic">Sin visitas programadas.</p>
            @endforelse
            @if ($moreUpcoming > 0)
                <a href="{{ route('pacientes.visitas', $patient) }}" wire:navigate class="block mt-1 text-xs text-teal-600 dark:text-teal-400 hover:underline">
                    + {{ $moreUpcoming }} más · Ver todas →
                </a>
            @endif

            @if ($canEdit)
                @if ($showScheduleForm)
                    <div class="mt-3 rounded-lg border border-teal-200 dark:border-teal-800 bg-teal-50/60 dark:bg-teal-900/10 p-3 space-y-2" dusk="schedule-form">
                        <div class="grid grid-cols-1 sm:grid-cols-[auto_1fr] gap-2">
                            <input
                                type="date"
                                wire:model="newDate"
                                min="{{ $today }}"
                                class="px-2 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm"
                            />
                            <input
                                type="text"
                                wire:model="newReason"
                                wire:keydown.enter="scheduleVisit"
                                maxlength="255"
                                placeholder="Motivo (ej: Control, Traer laboratorio)"
                                class="px-2 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-sm"
                            />
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @foreach (['Control', 'Traer laboratorio', 'Revisión'] as $quick)
                                <button
                                    type="button"
                                    wire:click="$set('newReason', '{{ $quick }}')"
                                    class="px-2 py-0.5 rounded-full border border-teal-200 dark:border-teal-800 text-xs text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-900/30"
                                >
                                    {{ $quick }}
                                </button>
                            @endforeach
                        </div>
                        @if ($errorMessage)
                            <p class="text-xs text-red-600 dark:text-red-400">{{ $errorMessage }}</p>
                        @endif
                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="scheduleVisit"
                                wire:loading.attr="disabled"
                                class="px-3 py-1.5 rounded-md bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium disabled:opacity-50"
                            >
                                Programar
                            </button>
                            <button
                                type="button"
                                wire:click="cancelSchedule"
                                class="px-3 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-600 text-sm text-zinc-600 dark:text-zinc-300"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                @else
                    <button
                        type="button"
                        wire:click="openSchedule"
                        class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-teal-600 dark:text-teal-400 hover:underline"
                    >
                        <flux:icon.plus class="size-4" />
                        Programar visita
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>
