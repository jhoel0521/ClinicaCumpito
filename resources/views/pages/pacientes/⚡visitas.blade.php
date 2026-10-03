<?php

use App\Contracts\ScheduledVisitServiceContract;
use App\Models\Patient;
use App\ValueObjects\VisitStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Historial completo de visitas programadas del paciente (pasadas y futuras).
 * En el perfil solo se muestran las activas; aquí entran todas, con filtro.
 */
new class extends Component {
    use WithPagination;

    public const PER_PAGE = 15;

    public Patient $patient;

    #[Url(as: 'estado')]
    public string $filter = 'all';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view', $this->patient), 403);
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function deleteVisit(string $visitId): void
    {
        abort_unless(auth()->user()?->can('update', $this->patient), 403);

        try {
            $this->patient->scheduledVisits()->findOrFail($visitId);
            app(ScheduledVisitServiceContract::class)->delete($visitId);
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function with(): array
    {
        $rows = app(ScheduledVisitServiceContract::class)->listForPatient($this->patient->id);

        $summary = VisitStatus::summarize($rows->pluck('status'));

        $filtered = $this->filter === 'all'
            ? $rows
            : $rows->filter(fn ($row) => $row['status']->value() === $this->filter)->values();

        // Más recientes primero; las pendientes futuras arriba.
        $filtered = $filtered->sortByDesc(fn ($row) => $row['visit']->scheduled_for->format('Y-m-d'))->values();

        $page = $this->getPage();

        return [
            'visits' => new LengthAwarePaginator(
                $filtered->forPage($page, self::PER_PAGE)->values(),
                $filtered->count(),
                self::PER_PAGE,
                $page,
            ),
            'summary' => $summary,
            'total' => $rows->count(),
            'canEdit' => auth()->user()?->can('update', $this->patient) ?? false,
        ];
    }
}; ?>

@php
    $filters = [
        'all' => 'Todas',
        VisitStatus::PENDING => 'Pendientes',
        VisitStatus::ON_TIME => 'A tiempo',
        VisitStatus::LATE => 'En el mes',
        VisitStatus::MISSED => 'No vino',
    ];
    $badge = [
        VisitStatus::PENDING => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800',
        VisitStatus::ON_TIME => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800',
        VisitStatus::LATE => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-900/20 dark:text-sky-300 dark:border-sky-800',
        VisitStatus::MISSED => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
    ];
@endphp

<section class="p-6">
    <div class="mb-6 flex items-center gap-4">
        <a
            href="{{ route('pacientes.show', $patient) }}#controles-mensuales"
            class="text-sm text-teal-600 dark:text-teal-400 hover:underline font-medium"
        >
            ← Volver al perfil
        </a>
        <div>
            <flux:heading size="xl">{{ __('Visitas de :name', ['name' => $patient->full_name]) }}</flux:heading>
            <flux:subheading>{{ __('Visitas programadas y si las cumplió') }}</flux:subheading>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2" dusk="visit-filters">
        @foreach ($filters as $value => $label)
            <button
                type="button"
                wire:click="$set('filter', '{{ $value }}')"
                @class([
                    'px-3 py-1.5 rounded-full border text-sm transition',
                    'bg-teal-600 border-teal-600 text-white' => $filter === $value,
                    'border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800' => $filter !== $value,
                ])
            >
                {{ $label }}
                <span class="ml-1 text-xs opacity-75">{{ $value === 'all' ? $total : $summary[$value] }}</span>
            </button>
        @endforeach
    </div>

    @if ($visits->isEmpty())
        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-10 text-center">
            <p class="text-zinc-400 dark:text-zinc-500 text-sm">
                {{ $total === 0 ? 'Este paciente no tiene visitas programadas.' : 'No hay visitas con este estado.' }}
            </p>
        </div>
    @else
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <table class="w-full text-sm" dusk="visits-table">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800/50 text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        <th class="px-4 py-2 text-left font-medium">Fecha</th>
                        <th class="px-4 py-2 text-left font-medium">Motivo</th>
                        <th class="px-4 py-2 text-left font-medium">Estado</th>
                        <th class="px-4 py-2 text-left font-medium">Vino el</th>
                        <th class="px-2 py-2 w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($visits as $row)
                        <tr wire:key="visit-{{ $row['visit']->id }}" dusk="visit-row">
                            <td class="px-4 py-2 font-medium text-zinc-800 dark:text-zinc-200 whitespace-nowrap">
                                {{ $row['visit']->scheduled_for->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-2 text-zinc-700 dark:text-zinc-300">{{ $row['visit']->reason }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full border text-xs font-medium {{ $badge[$row['status']->value()] }}">
                                    {{ $row['status']->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                {{ $row['status']->attendedOn()?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-2 py-2 text-center">
                                @if ($canEdit && $row['status']->isPending())
                                    <button
                                        type="button"
                                        wire:click="deleteVisit('{{ $row['visit']->id }}')"
                                        data-swal-confirm="¿Quitar esta visita programada?"
                                        class="text-zinc-400 hover:text-red-500 transition"
                                        title="Quitar visita"
                                    >
                                        <flux:icon.trash class="size-4" />
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $visits->links() }}</div>
    @endif
</section>
