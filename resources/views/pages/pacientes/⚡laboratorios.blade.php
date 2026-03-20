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
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex gap-3 items-center text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $consultation->consultation_date->format('d/m/Y') }}
                            </span>
                            <span>{{ $consultation->doctor?->full_name ?? '—' }}</span>
                        </div>
                        <a
                            href="{{ route('consultas.show', $consultation->id) }}"
                            class="text-xs text-teal-600 dark:text-teal-400 hover:underline"
                            wire:navigate
                        >
                            Ver consulta →
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($consultation->laboratoryRequests as $lab)
                            @php
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

                            <div class="flex items-start justify-between gap-3">
                                <ul class="space-y-1 text-sm flex-1">
                                    @foreach ($lab->items as $item)
                                        <li class="flex gap-3 items-start">
                                            <span class="text-blue-400 flex-shrink-0">🧪</span>
                                            <div class="text-zinc-700 dark:text-zinc-300">
                                                <span class="font-medium">{{ $item->exam_name }}</span>
                                                @if ($item->parameter_name)
                                                    <span class="text-zinc-500 dark:text-zinc-400">
                                                        — {{ $item->parameter_name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <span
                                    class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $labStatusClass }}"
                                >
                                    {{ $labStatusLabel }}
                                </span>
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
