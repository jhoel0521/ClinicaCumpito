<?php

use App\Models\Consultation;
use App\Models\Patient;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component {
    public Patient $patient;

    public string $month = '';

    public function mount(Patient $patient): void
    {
        abort_unless(
            auth()
                ->user()
                ?->can('view', $patient),
            403,
        );

        $this->patient = $patient;
        $this->month = now()->format('Y-m');
    }

    public function prevMonth(): void
    {
        $this->month = Carbon::parse($this->month . '-01')
            ->subMonth()
            ->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = Carbon::parse($this->month . '-01')
            ->addMonth()
            ->format('Y-m');
    }

    public function with(): array
    {
        $monthStart = Carbon::parse($this->month . '-01')->startOfMonth();

        $consultations = Consultation::query()
            ->where('patient_id', $this->patient->id)
            ->whereIn('status', ['saved', 'finalized'])
            ->whereYear('consultation_date', $monthStart->year)
            ->whereMonth('consultation_date', $monthStart->month)
            ->orderBy('consultation_date')
            ->get()
            ->keyBy(fn ($c) => $c->consultation_date->format('Y-m-d'));

        return [
            'monthStart' => $monthStart,
            'monthLabel' => $monthStart->isoFormat('MMMM YYYY'),
            'consultationsByDay' => $consultations,
            'today' => now()->format('Y-m-d'),
        ];
    }
}; ?>

<div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-zinc-800 dark:text-zinc-100">Calendario de consultas</h3>
        <div class="flex items-center gap-2">
            <button
                wire:click="prevMonth"
                class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                title="Mes anterior"
            >
                <flux:icon.chevron-left class="size-4" />
            </button>
            <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 capitalize min-w-28 text-center">
                {{ $monthLabel }}
            </span>
            <button
                wire:click="nextMonth"
                class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                title="Mes siguiente"
            >
                <flux:icon.chevron-right class="size-4" />
            </button>
        </div>
    </div>

    <div
        class="grid grid-cols-7 gap-1 text-center text-[10px] font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-1"
    >
        <span>Lun</span>
        <span>Mar</span>
        <span>Mié</span>
        <span>Jue</span>
        <span>Vie</span>
        <span>Sáb</span>
        <span>Dom</span>
    </div>

    <div class="grid grid-cols-7 gap-1" dusk="consultation-calendar">
        @php
            $firstDay = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
            $daysInMonth = $monthStart->daysInMonth;
            $currentMonth = $monthStart->month;
        @endphp

        @for ($i = 0; $i < 42; $i++)
            @php
                $date = $firstDay->copy()->addDays($i);
                $inMonth = $date->month === $currentMonth;
                $dateKey = $date->format('Y-m-d');
                $consultation = $consultationsByDay->get($dateKey);
                $isToday = $dateKey === $today;
                $isPast = $date->lt(now()->startOfDay());
            @endphp

            @if (! $inMonth)
                <div class="aspect-square rounded-lg bg-transparent"></div>
            @elseif ($consultation)
                <a
                    href="{{ route('consultas.show', $consultation->id) }}"
                    @class([
                        'flex items-center justify-center aspect-square rounded-lg text-xs font-bold transition hover:scale-105',
                        'bg-teal-500 text-white shadow-sm shadow-teal-500/30' => ! $isPast,
                        'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300' => $isPast,
                    ])
                    title="{{ $consultation->consultation_date->format('d/m/Y') }} — Consulta"
                    dusk="calendar-consultation-{{ $date->format('d') }}"
                >
                    {{ $date->day }}
                </a>
            @else
                <div
                    @class([
                        'flex items-center justify-center aspect-square rounded-lg text-xs',
                        'bg-teal-500/10 text-teal-700 dark:text-teal-300 ring-1 ring-inset ring-teal-500/30 font-bold' => $isToday,
                        'text-zinc-400 dark:text-zinc-600' => ! $isToday,
                    ])
                >
                    {{ $date->day }}
                </div>
            @endif
        @endfor
    </div>

    <div class="mt-3 flex items-center gap-4 text-[11px] text-zinc-500 dark:text-zinc-400">
        <span class="inline-flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-teal-100 dark:bg-teal-900/40 inline-block"></span>
            Consulta pasada
        </span>
        <span class="inline-flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-teal-500 inline-block"></span>
            Consulta reciente
        </span>
    </div>
</div>
