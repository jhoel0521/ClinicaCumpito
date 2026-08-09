<?php

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Prescription;
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
            'prescriptions' => Prescription::query()
                ->whereHas('consultation', fn ($q) => $q->where('patient_id', $this->patient->id))
                ->with(['items', 'consultation.doctor'])
                ->orderByDesc(
                    Consultation::select('consultation_date')
                        ->whereColumn('consultations.id', 'prescriptions.consultation_id')
                        ->limit(1),
                )
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
            <flux:heading size="xl">{{ __('Recetas de :name', ['name' => $patient->full_name]) }}</flux:heading>
            <flux:subheading>{{ __('Historial completo de recetas médicas') }}</flux:subheading>
        </div>
    </div>

    @if ($prescriptions->isEmpty())
        <div
            class="bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-10 text-center"
        >
            <p class="text-zinc-400 dark:text-zinc-500 text-sm">Este paciente no tiene recetas registradas.</p>
        </div>
    @else
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead
                    class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wide"
                >
                    <tr>
                        <th class="text-left px-4 py-3">Fecha</th>
                        <th class="text-left px-4 py-3">Diagnóstico</th>
                        <th class="text-center px-4 py-3">Medicamentos</th>
                        <th class="text-right px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($prescriptions as $prescription)
                        @php
                            $medCount = $prescription->items->count();
                            $consultation = $prescription->consultation;
                        @endphp

                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $consultation?->consultation_date?->format('d/m/Y') ?? '—' }}
                                </span>
                                @if ($consultation?->doctor)
                                    <span class="block text-xs text-zinc-400 dark:text-zinc-500">
                                        {{ $consultation->doctor->full_name }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-zinc-700 dark:text-zinc-300">
                                    {{ $prescription->reason ?: 'Receta médica' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300"
                                >
                                    💊 {{ $medCount }} {{ Str::plural('medicamento', $medCount) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-3">
                                    @if ($consultation)
                                        <a
                                            href="{{ route('consultas.pdf.recetas.single', [$consultation, $prescription]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:underline"
                                            dusk="prescription-pdf-{{ $prescription->id }}"
                                        >
                                            <flux:icon.document-arrow-down class="size-3.5" />
                                            PDF
                                        </a>
                                        <a
                                            href="{{ route('consultas.show', $consultation->id) }}#receta"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-teal-600 dark:text-teal-400 hover:underline"
                                            wire:navigate
                                        >
                                            Ver consulta
                                            <flux:icon.arrow-right class="size-3" />
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-zinc-400 dark:text-zinc-500">
                                No hay recetas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $prescriptions->links() }}
        </div>
    @endif
</section>
