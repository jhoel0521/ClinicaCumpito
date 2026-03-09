<?php

use App\Contracts\VitalSignServiceContract;
use App\DTOs\VitalSignDTO;
use App\Models\Consultation;
use App\ValueObjects\ConsultationStatus;
use Livewire\Component;

new class extends Component {
    public string $consultationId;

    public ?float $weight = null;

    public ?float $height = null;

    public ?float $head_circumference = null;

    public ?float $temperature = null;

    public bool $finalized = false;

    public bool $saved = false;

    public string $errorMessage = '';

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;

        $consultation = Consultation::with('vitalSigns')->findOrFail($consultationId);
        $this->finalized =
            $consultation->status instanceof ConsultationStatus
                ? $consultation->status->isFinalized()
                : (string) $consultation->status === ConsultationStatus::FINALIZED;

        $vs = $consultation->vitalSigns;
        if ($vs) {
            $this->weight = $vs->weight?->value();
            $this->height = $vs->height?->value();
            $this->head_circumference = $vs->head_circumference?->value();
            $this->temperature = $vs->temperature?->value();
            $this->saved = true;
        }
    }

    public function save(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        $this->validate(
            [
                'weight' => ['nullable', 'numeric', 'min:0', 'max:300'],
                'height' => ['nullable', 'numeric', 'min:0', 'max:250'],
                'head_circumference' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            ],
            [
                'weight.numeric' => 'El peso debe ser un número.',
                'height.numeric' => 'La talla debe ser un número.',
                'head_circumference.numeric' => 'El perímetro cefálico debe ser un número.',
                'temperature.numeric' => 'La temperatura debe ser un número.',
                'temperature.min' => 'La temperatura mínima es 30°C.',
                'temperature.max' => 'La temperatura máxima es 45°C.',
            ],
        );

        try {
            $dto = new VitalSignDTO(
                weight: $this->weight,
                height: $this->height,
                head_circumference: $this->head_circumference,
                temperature: $this->temperature,
            );

            app(VitalSignServiceContract::class)->upsert($this->consultationId, $dto);
            $this->saved = true;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar: ' . $e->getMessage();
        }
    }
}; ?>

<section id="signos-vitales" dusk="section-vital-signs" class="scroll-mt-16">
    <div
        class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                    <svg
                        class="w-4 h-4 text-blue-600 dark:text-blue-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        />
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Signos Vitales</h2>
            </div>
            <div class="flex items-center gap-2">
                @if ($finalized)
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400"
                    >
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 15v2m0 0v2m0-2h2m-2 0H10M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
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
                <span wire:loading wire:target="save" class="text-xs text-blue-400 dark:text-blue-500">Guardando…</span>
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

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                    >
                        Peso (kg)
                    </label>
                    <input
                        wire:model="weight"
                        wire:change="save"
                        type="number"
                        step="0.01"
                        min="0"
                        max="300"
                        placeholder="—"
                        dusk="vs-weight"
                        @disabled($finalized)
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    />
                    @error('weight')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                    >
                        Talla (cm)
                    </label>
                    <input
                        wire:model="height"
                        wire:change="save"
                        type="number"
                        step="0.01"
                        min="0"
                        max="250"
                        placeholder="—"
                        dusk="vs-height"
                        @disabled($finalized)
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    />
                    @error('height')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                    >
                        P. Cefálico (cm)
                    </label>
                    <input
                        wire:model="head_circumference"
                        wire:change="save"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        placeholder="—"
                        dusk="vs-head-circumference"
                        @disabled($finalized)
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    />
                    @error('head_circumference')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                    >
                        Temperatura (°C)
                    </label>
                    <input
                        wire:model="temperature"
                        wire:change="save"
                        type="number"
                        step="0.01"
                        min="30"
                        max="45"
                        placeholder="—"
                        dusk="vs-temperature"
                        @disabled($finalized)
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    />
                    @error('temperature')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</section>
