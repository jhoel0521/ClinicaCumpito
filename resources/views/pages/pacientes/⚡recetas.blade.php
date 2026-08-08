<?php

use App\Models\Consultation;
use App\Models\Patient;
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
            'consultations' => Consultation::query()
                ->where('patient_id', $this->patient->id)
                ->whereHas('prescriptions.items')
                ->orderByDesc('consultation_date')
                ->with(['prescriptions.items', 'doctor'])
                ->paginate(10),
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

    @if ($consultations->isEmpty())
        <div
            class="bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-10 text-center"
        >
            <p class="text-zinc-400 dark:text-zinc-500 text-sm">Este paciente no tiene recetas registradas.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($consultations as $consultation)
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex gap-3 items-center text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $consultation->consultation_date->format('d/m/Y') }}
                            </span>
                            <span>{{ $consultation->doctor?->full_name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($consultation->prescriptions->isNotEmpty())
                                <a
                                    href="{{ route('consultas.pdf.recetas.all', $consultation) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:underline"
                                    dusk="prescription-pdf-{{ $consultation->id }}"
                                >
                                    <flux:icon.document-arrow-down class="size-3.5" />
                                    PDF
                                </a>
                            @endif

                            <a
                                href="{{ route('consultas.show', $consultation->id) }}#receta"
                                class="text-xs text-teal-600 dark:text-teal-400 hover:underline"
                                wire:navigate
                            >
                                Ver consulta →
                            </a>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        @forelse ($consultation->prescriptions as $prescription)
                            @php
                                $medCount = $prescription->items->count();
                            @endphp

                            <div
                                class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 px-3 py-2"
                            >
                                <div class="flex items-center gap-2 min-w-0">
                                    <flux:icon.clipboard-document-list class="size-4 text-purple-400 shrink-0" />
                                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate">
                                        {{ $prescription->reason ?: 'Receta médica' }}
                                    </span>
                                </div>
                                <span
                                    class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300"
                                >
                                    💊 {{ $medCount }} {{ Str::plural('medicamento', $medCount) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-400 dark:text-zinc-500">Receta sin medicamentos registrados.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $consultations->links() }}
        </div>
    @endif
</section>
