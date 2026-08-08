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
                ->with(['vaccine', 'appliedByDoctor', 'consultation'])
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
                        <th class="text-left px-4 py-3">Fecha de aplicación</th>
                        <th class="text-left px-4 py-3">Vacuna</th>
                        <th class="text-center px-4 py-3">Dosis</th>
                        <th class="text-right px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($vaccines as $pv)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $pv->applied_at?->format('d/m/Y') ?? '—' }}
                                </span>
                                @if ($pv->appliedByDoctor)
                                    <span class="block text-xs text-zinc-400 dark:text-zinc-500">
                                        {{ $pv->appliedByDoctor->full_name }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-800 dark:text-zinc-200 font-medium">
                                {{ $pv->vaccine->name }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if ($pv->dose_number)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300"
                                    >
                                        {{ $pv->dose_number }}ª
                                    </span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                @if ($pv->consultation)
                                    <a
                                        href="{{ route('consultas.show', $pv->consultation->id) }}#vacunas"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-teal-600 dark:text-teal-400 hover:underline"
                                        wire:navigate
                                        dusk="vaccine-consultation-{{ $pv->id }}"
                                    >
                                        Ver consulta
                                        <flux:icon.arrow-right class="size-3" />
                                    </a>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">—</span>
                                @endif
                            </td>
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
