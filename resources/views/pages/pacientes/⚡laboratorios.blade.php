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
                ->whereHas('laboratoryRequests.items')
                ->orderByDesc('consultation_date')
                ->with(['laboratoryRequests.items', 'doctor'])
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
            <flux:heading size="xl">{{ __('Laboratorios de :name', ['name' => $patient->full_name]) }}</flux:heading>
            <flux:subheading>{{ __('Historial completo de solicitudes de laboratorio') }}</flux:subheading>
        </div>
    </div>

    @if ($consultations->isEmpty())
        <div
            class="bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-10 text-center"
        >
            <p class="text-zinc-400 dark:text-zinc-500 text-sm">
                Este paciente no tiene solicitudes de laboratorio registradas.
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($consultations as $consultation)
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
                >
                    {{-- Cabecera de la consulta --}}
                    <div
                        class="flex items-center gap-3 px-5 py-3 bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-700 text-sm"
                    >
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                            {{ $consultation->consultation_date->format('d/m/Y') }}
                        </span>
                        <span class="text-zinc-400">·</span>
                        <span class="text-zinc-500 dark:text-zinc-400">
                            {{ $consultation->doctor?->full_name ?? '—' }}
                        </span>
                    </div>

                    {{-- Una fila por orden de laboratorio --}}
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($consultation->laboratoryRequests as $lab)
                            @php
                                $examName = $lab->items->first()?->exam_name ?? 'Sin examen';
                                $count = $lab->items->count();
                                $days = (int) $lab->created_at->diffInDays(now());
                                $isPending = $lab->status === 'pending';
                                $isUrgent = $isPending && $days >= 3;
                                $labStatusClass = match (true) {
                                    $isUrgent => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    $isPending => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                    default => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                };
                                $labStatusLabel = $isPending ? "Pendiente · {$days}d" : 'Recibido';
                            @endphp

                            <div class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="flex items-center gap-2 text-sm min-w-0">
                                    <span class="text-blue-400 flex-shrink-0">🧪</span>
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $examName }}</span>
                                    <span class="text-zinc-400 dark:text-zinc-500 text-xs flex-shrink-0">
                                        · {{ $count }} parámetro{{ $count !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $labStatusClass }}"
                                    >
                                        {{ $labStatusLabel }}
                                    </span>
                                    <a
                                        href="{{ route('pacientes.laboratorios.show', [$patient, $lab]) }}"
                                        class="text-xs text-teal-600 dark:text-teal-400 hover:underline font-medium"
                                        wire:navigate
                                    >
                                        Ver →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $consultations->links() }}
        </div>
    @endif
</section>
