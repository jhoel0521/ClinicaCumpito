<?php

use App\Models\Patient;
use App\Models\PatientVaccine;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public Patient $patient;

    public function mount(): void
    {
        abort_unless(
            auth()
                ->user()
                ?->can('view', $this->patient),
            403,
        );
    }

    public function with(): array
    {
        return [
            'vaccines' => PatientVaccine::query()
                ->where('patient_id', $this->patient->id)
                ->with(['vaccine', 'appliedByDoctor'])
                ->orderByDesc('applied_at')
                ->paginate(15),
        ];
    }
}; ?>

<section class="p-6">
    <div class="mb-6 flex items-center gap-4">
        <a
            href="{{ route('pacientes.show', $patient) }}"
            class="text-sm text-teal-600 dark:text-teal-400 hover:underline font-medium"
        >
            ← Volver al perfil
        </a>
        <div>
            <flux:heading size="xl">{{ __('Vacunas de :name', ['name' => $patient->full_name]) }}</flux:heading>
            <flux:subheading>{{ __('Historial completo de vacunación') }}</flux:subheading>
        </div>
    </div>

    @if ($vaccines->isEmpty())
        <div
            class="bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-10 text-center"
        >
            <p class="text-zinc-400 dark:text-zinc-500 text-sm">
                Este paciente no tiene vacunas registradas en su historial.
            </p>
        </div>
    @else
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead
                    class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wide"
                >
                    <tr>
                        <th class="text-left px-4 py-3">Fecha de Admin.</th>
                        <th class="text-left px-4 py-3">Vacuna</th>
                        <th class="text-left px-4 py-3">Dosis</th>
                        <th class="text-left px-4 py-3">Administrado por</th>
                        <th class="text-left px-4 py-3">Próxima dosis</th>
                        <th class="text-left px-4 py-3">Lote</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($vaccines as $pv)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $pv->applied_at?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-800 dark:text-zinc-200 font-medium">
                                {{ $pv->vaccine->name }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $pv->dose_number }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $pv->applied_elsewhere ? 'Otro lugar' : $pv->appliedByDoctor?->full_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">—</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">—</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $vaccines->links() }}
        </div>
    @endif
</section>
