<?php

use App\Contracts\ScheduledVisitServiceContract;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Agenda del dashboard: visitas programadas de los próximos 7, 15 o 30 días
 * y las atrasadas (la fecha pasó y el paciente no vino) para recordarles.
 * Muestra las visitas del doctor del usuario; si el usuario no es doctor, las
 * de todos.
 */
new class extends Component {
    public const RANGES = [7 => '7 días', 15 => '15 días', 30 => 'Mes'];

    #[Url(as: 'agenda')]
    public int $days = 7;

    public ?string $selectedDay = null;

    public function setRange(int $days): void
    {
        $this->days = array_key_exists($days, self::RANGES) ? $days : 7;
        $this->selectedDay = null;
    }

    public function selectDay(string $day): void
    {
        $this->selectedDay = $this->selectedDay === $day ? null : $day;
    }

    public function with(): array
    {
        $days = array_key_exists($this->days, self::RANGES) ? $this->days : 7;
        $today = CarbonImmutable::today();
        $until = $today->addDays($days - 1);
        $doctorId = auth()->user()?->doctor_id;
        $service = app(ScheduledVisitServiceContract::class);

        $agenda = $service->agenda($doctorId, $today->format('Y-m-d'), $until->format('Y-m-d'));
        $byDay = $agenda->groupBy(fn ($row) => $row['visit']->scheduled_for->format('Y-m-d'));

        $listed = $this->selectedDay !== null
            ? $byDay->only([$this->selectedDay])
            : $byDay;

        return [
            'rangeDays' => collect(range(0, $days - 1))->map(fn ($i) => $today->addDays($i)),
            'countByDay' => $byDay->map->count(),
            'listed' => $listed,
            'total' => $agenda->count(),
            'overdue' => $service->overdue($doctorId),
            'todayKey' => $today->format('Y-m-d'),
            'ranges' => self::RANGES,
        ];
    }
}; ?>

@php
    $dayLabel = function (string $key) use ($todayKey) {
        $date = CarbonImmutable::parse($key);

        return match (true) {
            $key === $todayKey => 'Hoy',
            $key === CarbonImmutable::parse($todayKey)->addDay()->format('Y-m-d') => 'Mañana',
            default => ucfirst($date->isoFormat('dddd D [de] MMMM')),
        };
    };
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" dusk="doctor-agenda">
    {{-- ── Agenda de los próximos días ── --}}
    <div class="lg:col-span-2 rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Mi agenda de visitas</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $total }} visita(s) programada(s)</p>
            </div>
            <div class="inline-flex rounded-lg border border-gray-200 dark:border-zinc-700 p-0.5" dusk="agenda-ranges">
                @foreach ($ranges as $value => $label)
                    <button
                        type="button"
                        wire:click="setRange({{ $value }})"
                        @class([
                            'px-3 py-1 rounded-md text-xs font-medium transition',
                            'bg-teal-600 text-white' => $days === $value,
                            'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700' => $days !== $value,
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Tira de días: cuántas visitas hay cada día (clic para filtrar) --}}
        <div class="grid grid-cols-7 gap-1 mb-4" dusk="agenda-days">
            @foreach ($rangeDays as $date)
                @php
                    $key = $date->format('Y-m-d');
                    $count = $countByDay->get($key, 0);
                @endphp
                <button
                    type="button"
                    wire:key="agenda-day-{{ $key }}"
                    wire:click="selectDay('{{ $key }}')"
                    @class([
                        'flex flex-col items-center rounded-lg border py-1.5 text-center transition',
                        'border-teal-500 ring-1 ring-teal-500' => $selectedDay === $key,
                        'border-teal-200 dark:border-teal-800 bg-teal-50 dark:bg-teal-900/20' => $count > 0 && $selectedDay !== $key,
                        'border-gray-100 dark:border-zinc-700' => $count === 0 && $selectedDay !== $key,
                    ])
                    title="{{ $date->format('d/m/Y') }} — {{ $count }} visita(s)"
                    dusk="agenda-day-{{ $key }}"
                >
                    <span class="text-[10px] uppercase text-gray-400 dark:text-gray-500">{{ $date->isoFormat('dd') }}</span>
                    <span @class(['text-sm font-bold', 'text-teal-700 dark:text-teal-300' => $key === $todayKey, 'text-gray-700 dark:text-gray-200' => $key !== $todayKey])>
                        {{ $date->day }}
                    </span>
                    <span @class(['text-[10px] font-semibold', 'text-teal-700 dark:text-teal-300' => $count > 0, 'text-transparent' => $count === 0])>
                        {{ $count }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Lista de visitas por día --}}
        <div class="space-y-3 max-h-80 overflow-y-auto" dusk="agenda-list">
            @forelse ($listed as $key => $rows)
                <div wire:key="agenda-group-{{ $key }}">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">
                        {{ $dayLabel($key) }}
                        <span class="font-normal">· {{ $rows->count() }}</span>
                    </p>
                    <ul class="divide-y divide-gray-100 dark:divide-zinc-700 rounded-lg border border-gray-100 dark:border-zinc-700">
                        @foreach ($rows as $row)
                            @php
                                $patient = $row['visit']->patient;
                                $phone = $patient?->user?->phone_number;
                            @endphp
                            <li wire:key="agenda-visit-{{ $row['visit']->id }}" class="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                                <span class="min-w-0">
                                    <a
                                        href="{{ route('pacientes.show', $patient) }}#controles-mensuales"
                                        class="font-medium text-gray-900 dark:text-white hover:text-teal-600 dark:hover:text-teal-400"
                                    >
                                        {{ $patient?->full_name }}
                                    </a>
                                    <span class="text-gray-500 dark:text-gray-400">· {{ $row['visit']->reason }}</span>
                                </span>
                                <span class="flex items-center gap-2 shrink-0">
                                    @if ($phone)
                                        <a href="tel:{{ $phone }}" class="text-xs text-teal-600 dark:text-teal-400 hover:underline">{{ $phone }}</a>
                                    @endif
                                    @unless ($row['status']->isPending())
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
                                            Ya vino
                                        </span>
                                    @endunless
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="text-sm text-gray-400 dark:text-gray-500 italic py-4 text-center">
                    {{ $selectedDay ? 'No hay visitas ese día.' : 'No hay visitas programadas en este período.' }}
                </p>
            @endforelse
        </div>
    </div>

    {{-- ── Atrasadas: debían venir y no vinieron ── --}}
    <div class="rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 p-5 shadow-sm" dusk="agenda-overdue">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Para recordar</h3>
            <span @class([
                'px-2 py-0.5 rounded-full text-xs font-semibold',
                'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300' => $overdue->isNotEmpty(),
                'bg-gray-100 text-gray-500 dark:bg-zinc-700 dark:text-gray-400' => $overdue->isEmpty(),
            ])>{{ $overdue->count() }}</span>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">Debían venir en los últimos 30 días y todavía no vinieron.</p>

        <ul class="space-y-2 max-h-96 overflow-y-auto">
            @forelse ($overdue as $row)
                @php
                    $patient = $row['visit']->patient;
                    $phone = $patient?->user?->phone_number;
                    $daysLate = (int) $row['visit']->scheduled_for->diffInDays(now()->startOfDay());
                @endphp
                <li wire:key="overdue-{{ $row['visit']->id }}" class="rounded-lg border border-red-100 dark:border-red-900/40 bg-red-50/40 dark:bg-red-900/10 px-3 py-2 text-sm">
                    <a
                        href="{{ route('pacientes.show', $patient) }}#controles-mensuales"
                        class="font-medium text-gray-900 dark:text-white hover:text-teal-600 dark:hover:text-teal-400"
                    >
                        {{ $patient?->full_name }}
                    </a>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $row['visit']->reason }} · debía venir el {{ $row['visit']->scheduled_for->format('d/m') }}
                        ({{ $daysLate === 1 ? 'hace 1 día' : "hace {$daysLate} días" }})
                    </p>
                    @if ($phone)
                        <a href="tel:{{ $phone }}" class="text-xs text-teal-600 dark:text-teal-400 hover:underline">📞 {{ $phone }}</a>
                    @endif
                </li>
            @empty
                <li class="text-sm text-gray-400 dark:text-gray-500 italic">Nadie atrasado. 👍</li>
            @endforelse
        </ul>
    </div>
</div>
