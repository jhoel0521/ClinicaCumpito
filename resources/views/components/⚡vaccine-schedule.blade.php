<?php

use App\Models\Patient;
use App\Models\PatientVaccine;
use App\Models\Vaccine;
use Livewire\Component;

new class extends Component {
    public Patient $patient;

    public function mount(Patient $patient): void
    {
        $this->patient = $patient;
    }

    public function with(): array
    {
        $ageInMonths =
            $this->patient->date_of_birth === null ? null : (int) $this->patient->date_of_birth->diffInMonths(now());

        $applied = PatientVaccine::query()
            ->where('patient_id', $this->patient->id)
            ->with('vaccine')
            ->orderByDesc('applied_at')
            ->get();

        $appliedKeys = $applied->map(fn ($pv) => $pv->vaccine_id . '|' . (string) $pv->dose_number)->all();

        $due = Vaccine::query()
            ->whereNotNull('min_age_months')
            ->when($ageInMonths !== null, fn ($q) => $q->where('min_age_months', '<=', $ageInMonths))
            ->orderBy('min_age_months')
            ->get()
            ->map(
                fn ($v) => [
                    'name' => $v->name,
                    'dose' => $v->dose_sequence ? $v->dose_sequence . 'ª dosis' : '—',
                    'recommended_age' => $v->recommended_age,
                    'min_age_months' => $v->min_age_months,
                    'applied' => $applied->first(
                        fn ($pv) => $pv->vaccine_id === $v->id &&
                            (string) $pv->dose_number === (string) $v->dose_sequence,
                    ),
                    'key' => $v->id . '|' . (string) $v->dose_sequence,
                ],
            )
            ->values()
            ->all();

        return [
            'ageInMonths' => $ageInMonths,
            'dueVaccines' => $due,
            'appliedKeys' => $appliedKeys,
        ];
    }
}; ?>

<div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-zinc-800 dark:text-zinc-100">Esquema de vacunación</h3>
        @if ($ageInMonths !== null)
            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                Edad: {{ $ageInMonths }} {{ $ageInMonths === 1 ? 'mes' : 'meses' }}
            </span>
        @endif
    </div>

    @if ($dueVaccines === [])
        <p class="text-sm text-zinc-400 dark:text-zinc-500">
            {{ $ageInMonths === null ? 'Registre la fecha de nacimiento para ver el esquema.' : 'Sin vacunas correspondientes a la edad del paciente.' }}
        </p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead
                    class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wide"
                >
                    <tr>
                        <th class="text-left px-4 py-2">Vacuna</th>
                        <th class="text-left px-4 py-2">Dosis</th>
                        <th class="text-left px-4 py-2">Edad recomendada</th>
                        <th class="text-right px-4 py-2">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($dueVaccines as $v)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-4 py-2 font-medium text-zinc-800 dark:text-zinc-200">{{ $v['name'] }}</td>
                            <td class="px-4 py-2 text-zinc-600 dark:text-zinc-400">{{ $v['dose'] }}</td>
                            <td class="px-4 py-2 text-zinc-600 dark:text-zinc-400">{{ $v['recommended_age'] }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">
                                @if ($v['applied'])
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300"
                                    >
                                        <flux:icon.check class="size-3" />
                                        {{ $v['applied']->applied_at->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300"
                                    >
                                        <flux:icon.clock class="size-3" />
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
