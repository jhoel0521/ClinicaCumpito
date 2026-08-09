<?php

use App\Contracts\SoapNoteServiceContract;
use App\DTOs\SoapNoteDTO;
use App\Models\Consultation;
use App\ValueObjects\ConsultationStatus;
use Livewire\Component;

new class extends Component {
    public string $consultationId;

    public ?string $subjective = null;

    public ?string $objective = null;

    public ?string $assessment = null;

    public ?string $plan = null;

    public bool $finalized = false;

    public bool $saved = false;

    public string $errorMessage = '';

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;

        $consultation = Consultation::with('soapNote')->findOrFail($consultationId);
        $this->finalized =
            $consultation->status instanceof ConsultationStatus
                ? $consultation->status->isFinalized()
                : (string) $consultation->status === ConsultationStatus::FINALIZED;

        $soap = $consultation->soapNote;
        if ($soap) {
            $this->subjective = $soap->subjective;
            $this->objective = $soap->objective;
            $this->assessment = $soap->assessment;
            $this->plan = $soap->plan;
            $this->saved = true;
        }
    }

    public function save(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        $this->validate([
            'subjective' => ['nullable', 'string', 'max:2000'],
            'objective' => ['nullable', 'string', 'max:2000'],
            'assessment' => ['nullable', 'string', 'max:2000'],
            'plan' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $dto = new SoapNoteDTO(
                subjective: $this->subjective ?: null,
                objective: $this->objective ?: null,
                assessment: $this->assessment ?: null,
                plan: $this->plan ?: null,
            );

            app(SoapNoteServiceContract::class)->upsert($this->consultationId, $dto);
            $this->saved = true;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }
}; ?>

<section id="soap" dusk="section-soap" class="scroll-mt-16">
    <div
        class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                    <svg
                        class="w-4 h-4 text-purple-600 dark:text-purple-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Nota SOAP</h2>
            </div>
            <div class="flex items-center gap-2">
                @if ($finalized)
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400"
                    >
                        Finalizada
                    </span>
                @elseif ($saved)
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300"
                    >
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Guardado
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300"
                    >
                        Sin datos
                    </span>
                @endif
                <span wire:loading wire:target="save" class="text-xs text-purple-400 dark:text-purple-500">
                    Guardando…
                </span>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            @if ($errorMessage)
                <div
                    class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300"
                >
                    {{ $errorMessage }}
                </div>
            @endif

            <div class="space-y-4">
                @foreach ([
                        [
                            'key' => 'subjective',
                            'initial' => 'S',
                            'name' => 'Subjetivo',
                            'placeholder' => 'subjetivo',
                            'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                            'dusk' => 'soap-subjective'
                        ],
                        [
                            'key' => 'objective',
                            'initial' => 'O',
                            'name' => 'Objetivo',
                            'placeholder' => 'objetivo',
                            'badge' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                            'dusk' => 'soap-objective'
                        ],
                        [
                            'key' => 'assessment',
                            'initial' => 'A',
                            'name' => 'Análisis',
                            'placeholder' => 'análisis',
                            'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                            'dusk' => 'soap-assessment'
                        ],
                        [
                            'key' => 'plan',
                            'initial' => 'P',
                            'name' => 'Plan',
                            'placeholder' => 'plan',
                            'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                            'dusk' => 'soap-plan'
                        ]
                    ]
                    as $field)
                    <div>
                        <label class="flex items-center gap-2 text-xs font-semibold mb-1.5">
                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $field['badge'] }}">
                                {{ $field['initial'] }}
                            </span>
                            <span class="text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                                {{ $field['name'] }}
                            </span>
                        </label>
                        <textarea
                            wire:model="{{ $field['key'] }}"
                            wire:change="save"
                            rows="3"
                            dusk="{{ $field['dusk'] }}"
                            @disabled($finalized)
                            placeholder="Ingrese {{ $field['placeholder'] }}..."
                            class="w-full px-3 py-2.5 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm resize-y focus:ring-2 focus:ring-purple-500 focus:border-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        ></textarea>
                        @error($field['key'])
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
