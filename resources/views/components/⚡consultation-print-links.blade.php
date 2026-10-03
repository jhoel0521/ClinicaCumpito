<?php

use App\Models\LaboratoryExam;
use App\Models\LaboratoryRequest;
use App\Models\Prescription;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Links de "imprimir" de la columna izquierda de la consulta (recetas o
 * laboratorios). Están donde la doctora siempre los buscó, pero ahora se
 * actualizan en vivo: los componentes de receta y laboratorio emiten
 * `prescriptions-changed` / `lab-requests-changed` y esta lista se vuelve a
 * dibujar, sin finalizar ni recargar con F5.
 */
new class extends Component {
    #[Locked]
    public string $consultationId;

    /** 'recetas' | 'laboratorios' */
    #[Locked]
    public string $kind;

    #[Locked]
    public string $patientId = '';

    public function mount(string $consultationId, string $kind, string $patientId = ''): void
    {
        $this->consultationId = $consultationId;
        $this->kind = $kind;
        $this->patientId = $patientId;
    }

    #[On('prescriptions-changed')]
    public function refreshPrescriptions(): void
    {
        if ($this->kind !== 'recetas') {
            $this->skipRender();
        }
    }

    #[On('lab-requests-changed')]
    public function refreshLabRequests(): void
    {
        if ($this->kind !== 'laboratorios') {
            $this->skipRender();
        }
    }

    /** @return array<int, array{id: string, label: string, printable: bool}> */
    public function prescriptionLinks(): array
    {
        return Prescription::with('items')
            ->where('consultation_id', $this->consultationId)
            ->orderBy('created_at')
            ->get()
            ->values()
            ->map(fn (Prescription $rx, int $i) => [
                'id' => $rx->id,
                'label' => $rx->reason ?: 'Receta #' . ($i + 1),
                'printable' => $rx->items->contains(fn ($item) => trim((string) $item->medication_name) !== ''),
            ])
            ->all();
    }

    /** @return array<int, array{id: string, label: string, received: bool, categories: array<int, string>, exams: array<int, string>}> */
    public function labLinks(): array
    {
        $categoryByExam = LaboratoryExam::with('category')
            ->get()
            ->mapWithKeys(fn ($exam) => [$exam->name => $exam->category?->name ?? 'Otros']);

        return LaboratoryRequest::with('items')
            ->where('consultation_id', $this->consultationId)
            ->orderBy('created_at')
            ->get()
            ->values()
            ->map(fn (LaboratoryRequest $lab, int $i) => [
                'id' => $lab->id,
                'label' => 'Solicitud #' . ($i + 1),
                'received' => $lab->status === 'received',
                'exams' => $lab->examNames(),
                'categories' => collect($lab->examNames())
                    ->map(fn ($name) => $categoryByExam[$name] ?? 'Otros')
                    ->unique()
                    ->values()
                    ->all(),
            ])
            ->all();
    }
}; ?>

@php
    $chip = 'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 transition';
    $printIcon = 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z';
@endphp

<div dusk="print-links-{{ $kind }}">
    @if ($kind === 'recetas')
        @php
            $links = $this->prescriptionLinks();
        @endphp
        @if ($links !== [])
            <div class="mt-3 space-y-1.5">
                @foreach ($links as $rx)
                    @if ($rx['printable'])
                        <a
                            wire:key="rx-{{ $rx['id'] }}"
                            href="{{ route('documentos.recetas.preview', $rx['id']) }}"
                            data-print-link
                            class="{{ $chip }}"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $printIcon }}" />
                            </svg>
                            {{ $rx['label'] }}
                        </a>
                    @else
                        <span
                            wire:key="rx-{{ $rx['id'] }}"
                            class="{{ $chip }} opacity-60 cursor-not-allowed hover:bg-white dark:hover:bg-zinc-800"
                            title="Agrega al menos un medicamento para imprimir"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $printIcon }}" />
                            </svg>
                            {{ $rx['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif
    @else
        @php
            $links = $this->labLinks();
        @endphp
        @if ($links !== [])
            <div class="mt-3 space-y-1.5">
                @foreach ($links as $lab)
                    <div wire:key="lab-{{ $lab['id'] }}" class="{{ $chip }} justify-between gap-2">
                        <a
                            href="{{ route('documentos.laboratorios.preview', $lab['id']) }}"
                            data-print-link
                            class="flex items-center gap-1.5 min-w-0"
                        >
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $printIcon }}" />
                            </svg>
                            {{ $lab['label'] }}
                            @if ($lab['received'])
                                <span class="ml-1 text-emerald-600 dark:text-emerald-400">(con resultados)</span>
                            @endif
                        </a>
                        <span class="flex items-center gap-2 shrink-0">
                            {{-- Imprimir solo una parte: por categoría o por examen --}}
                            @if (count($lab['exams']) > 1)
                                <details class="relative" dusk="lab-print-menu">
                                    <summary
                                        class="list-none cursor-pointer text-purple-600 dark:text-purple-400 hover:underline"
                                        title="Imprimir por categoría o por examen"
                                    >
                                        Partes ▾
                                    </summary>
                                    <div
                                        class="absolute left-0 z-20 mt-1 w-60 rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-lg py-1 text-sm font-normal"
                                    >
                                        @if (count($lab['categories']) > 1)
                                            <p class="px-3 pt-1 pb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                                Por categoría
                                            </p>
                                            @foreach ($lab['categories'] as $category)
                                                <a
                                                    href="{{ route('documentos.laboratorios.preview', ['laboratoryRequest' => $lab['id'], 'categoria' => $category]) }}"
                                                    data-print-link
                                                    class="block px-3 py-1.5 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-900/20"
                                                >
                                                    {{ $category }}
                                                </a>
                                            @endforeach
                                        @endif
                                        <p class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                            Por examen
                                        </p>
                                        @foreach ($lab['exams'] as $exam)
                                            <a
                                                href="{{ route('documentos.laboratorios.preview', ['laboratoryRequest' => $lab['id'], 'examen' => $exam]) }}"
                                                data-print-link
                                                class="block px-3 py-1.5 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-900/20"
                                            >
                                                {{ $exam }}
                                            </a>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                            <a
                                href="{{ route('pacientes.laboratorios.show', [$patientId, $lab['id']]) }}"
                                class="text-purple-600 dark:text-purple-400 hover:underline"
                                wire:navigate
                            >
                                Ver detalle
                            </a>
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
