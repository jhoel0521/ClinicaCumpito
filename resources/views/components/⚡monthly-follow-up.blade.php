<?php

use App\Models\Consultation;
use App\Models\Patient;
use Livewire\Component;

new class extends Component {
    public Patient $patient;

    public function mount(Patient $patient): void
    {
        abort_unless(
            auth()
                ->user()
                ?->can('view', $patient),
            403,
        );

        $this->patient = $patient;
    }

    /**
     * Matriz de controles mensuales 0-24 meses.
     * Cada mes con consulta registrada (saved/finalized) queda asociado
     * a la consulta más reciente de ese mes de edad. No se crean consultas.
     *
     * @return array<int, array{month: int, consultation?: Consultation}>
     */
    public function controls(): array
    {
        if ($this->patient->date_of_birth === null) {
            return [];
        }

        $consultations = Consultation::query()
            ->where('patient_id', $this->patient->id)
            ->whereIn('status', ['saved', 'finalized'])
            ->orderByDesc('consultation_date')
            ->get();

        $byMonth = [];

        foreach ($consultations as $consultation) {
            $age = $this->patient->ageAt($consultation->consultation_date);

            if ($age === null) {
                continue;
            }

            $month = $age->months();

            if ($month > 24) {
                continue;
            }

            // La primera consulta de la iteración es la más reciente del mes
            $byMonth[$month] ??= ['month' => $month, 'consultation' => $consultation];
        }

        $controls = [];

        for ($month = 0; $month <= 24; $month++) {
            $controls[$month] = $byMonth[$month] ?? ['month' => $month];
        }

        return $controls;
    }
}; ?>

<div>
    @php
        $controls = $this->controls();
        $completedCount = collect($controls)
            ->filter(fn ($c) => isset($c['consultation']))
            ->count();
    @endphp

    @if ($controls !== [])
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-zinc-800 dark:text-zinc-100">Controles mensuales 0–24 meses</h3>
                <span class="text-xs text-zinc-400 dark:text-zinc-500">
                    {{ $completedCount }}
                    de 25 controles
                </span>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2" dusk="monthly-controls">
                @foreach ($controls as $control)
                    @php
                        $hasConsultation = isset($control['consultation']);
                        $consultation = $control['consultation'] ?? null;
                        $label = $control['month'] === 0 ? 'Recién nacido' : 'Mes ' . $control['month'];
                    @endphp

                    @if ($hasConsultation)
                        <a
                            href="{{ route('consultas.show', $consultation->id) }}"
                            class="group flex flex-col items-center gap-1 rounded-xl border border-teal-200 dark:border-teal-800 bg-teal-50 dark:bg-teal-900/20 px-2 py-3 text-center transition hover:border-teal-400 dark:hover:border-teal-600 hover:shadow-sm"
                            title="{{ $consultation->consultation_date->format('d/m/Y') }}"
                            dusk="control-month-{{ $control['month'] }}"
                        >
                            <span
                                class="text-[10px] font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300"
                            >
                                {{ $label }}
                            </span>
                            <span class="text-xs font-medium text-teal-900 dark:text-teal-100 leading-tight">
                                {{ $consultation->consultation_date->format('d/m/Y') }}
                            </span>
                            <flux:icon.check-circle class="size-4 text-teal-500 group-hover:scale-110 transition" />
                        </a>
                    @else
                        <div
                            class="flex flex-col items-center gap-1 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 px-2 py-3 text-center"
                            dusk="control-missing-{{ $control['month'] }}"
                        >
                            <span
                                class="text-[10px] font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500"
                            >
                                {{ $label }}
                            </span>
                            <span class="text-xs text-zinc-300 dark:text-zinc-600">Sin control</span>
                            <flux:icon.x-mark class="size-4 text-zinc-300 dark:text-zinc-600" />
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
