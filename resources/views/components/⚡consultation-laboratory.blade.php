<?php

use App\Contracts\LaboratoryRequestItemServiceContract;
use App\Contracts\LaboratoryRequestServiceContract;
use App\DTOs\LaboratoryRequestDTO;
use App\DTOs\LaboratoryRequestItemDTO;
use App\Models\Consultation;
use App\Models\LaboratoryTemplate;
use App\ValueObjects\ConsultationStatus;
use Livewire\Component;

new class extends Component {
    public string $consultationId;

    /** @var array<int, array{id: string, name: string}> */
    public array $templates = [];

    public ?string $labRequestId = null;

    public ?string $sourceTemplateId = null;

    public ?string $observations = null;

    /** @var array<int, array{id: string, exam_name: string, indications: string|null}> */
    public array $items = [];

    public string $newExamName = '';

    public string $newIndications = '';

    public bool $finalized = false;

    public bool $saved = false;

    public string $errorMessage = '';

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;

        $consultation = Consultation::with('laboratoryRequest.items')->findOrFail($consultationId);

        $this->templates = LaboratoryTemplate::query()
            ->where('doctor_id', $consultation->doctor_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
            ->all();
        $this->finalized =
            $consultation->status instanceof ConsultationStatus
                ? $consultation->status->isFinalized()
                : (string) $consultation->status === ConsultationStatus::FINALIZED;

        $labReq = $consultation->laboratoryRequest;
        if ($labReq) {
            $this->labRequestId = $labReq->id;
            $this->sourceTemplateId = $labReq->source_template_id;
            $this->observations = $labReq->observations;
            $this->saved = true;
            $this->loadItems($labReq->items);
        }
    }

    /** @param \Illuminate\Support\Collection<int, \App\Models\LaboratoryRequestItem> $items */
    private function loadItems(\Illuminate\Support\Collection $items): void
    {
        $this->items = $items
            ->map(
                fn ($item) => [
                    'id' => $item->id,
                    'exam_name' => $item->exam_name,
                    'indications' => $item->indications,
                ],
            )
            ->values()
            ->all();
    }

    public function saveLabRequest(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        $this->validate([
            'sourceTemplateId' => ['nullable', 'string'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $dto = new LaboratoryRequestDTO(
                source_template_id: $this->sourceTemplateId ?: null,
                observations: $this->observations ?: null,
            );
            $labReq = app(LaboratoryRequestServiceContract::class)->upsert($this->consultationId, $dto);
            $this->labRequestId = $labReq->id;
            $this->saved = true;

            $fresh = $labReq->loadMissing('items');
            $this->loadItems($fresh->items);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar: ' . $e->getMessage();
        }
    }

    public function addItem(): void
    {
        if ($this->finalized || ! $this->labRequestId) {
            return;
        }

        $this->errorMessage = '';

        $this->validate(
            [
                'newExamName' => ['required', 'string', 'max:200'],
                'newIndications' => ['nullable', 'string', 'max:500'],
            ],
            [
                'newExamName.required' => 'El nombre del examen es obligatorio.',
            ],
        );

        try {
            $dto = new LaboratoryRequestItemDTO(
                exam_name: $this->newExamName,
                indications: $this->newIndications ?: null,
            );

            $item = app(LaboratoryRequestItemServiceContract::class)->create($this->labRequestId, $dto);

            $this->items[] = [
                'id' => $item->id,
                'exam_name' => $item->exam_name,
                'indications' => $item->indications,
            ];

            $this->newExamName = '';
            $this->newIndications = '';
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al agregar examen: ' . $e->getMessage();
        }
    }

    public function removeItem(string $itemId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            app(LaboratoryRequestItemServiceContract::class)->delete($itemId);
            $this->items = array_values(array_filter($this->items, fn ($i) => $i['id'] !== $itemId));
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar: ' . $e->getMessage();
        }
    }
}; ?>

<section id="laboratorio" dusk="section-laboratory" class="scroll-mt-16">
    <div
        class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center">
                    <svg
                        class="w-4 h-4 text-orange-600 dark:text-orange-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                        />
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Solicitud de Laboratorio</h2>
            </div>
            <div>
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
            </div>
        </div>

        <div class="p-6 space-y-6">
            @if ($errorMessage)
                <div
                    class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300"
                >
                    {{ $errorMessage }}
                </div>
            @endif

            {{-- Cabecera de solicitud --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                    >
                        Plantilla origen
                    </label>
                    <select
                        wire:model="sourceTemplateId"
                        dusk="lab-source-template"
                        @disabled($finalized)
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <option value="">Sin plantilla</option>
                        @foreach ($templates as $t)
                            <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                    >
                        Observaciones
                    </label>
                    <textarea
                        wire:model="observations"
                        rows="2"
                        dusk="lab-observations"
                        @disabled($finalized)
                        placeholder="Indicaciones generales..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm resize-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    ></textarea>
                </div>
            </div>

            @if (! $finalized)
                <div class="flex justify-end">
                    <button
                        wire:click="saveLabRequest"
                        wire:loading.attr="disabled"
                        dusk="lab-save-btn"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium transition disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="saveLabRequest">
                            {{ $saved ? 'Actualizar Solicitud' : 'Crear Solicitud' }}
                        </span>
                        <span wire:loading wire:target="saveLabRequest">Guardando...</span>
                    </button>
                </div>
            @endif

            {{-- Exámenes --}}
            @if ($labRequestId)
                <div class="border-t border-gray-100 dark:border-zinc-800 pt-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Exámenes Solicitados</h3>

                    @if (count($items) > 0)
                        <div class="space-y-2 mb-4">
                            @foreach ($items as $item)
                                <div
                                    class="flex items-start justify-between bg-gray-50 dark:bg-zinc-800 rounded-lg px-4 py-3 gap-3"
                                    dusk="lab-exam-item"
                                >
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $item['exam_name'] }}
                                        </p>
                                        @if ($item['indications'])
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $item['indications'] }}
                                            </p>
                                        @endif
                                    </div>
                                    @if (! $finalized)
                                        <button
                                            wire:click="removeItem('{{ $item['id'] }}')"
                                            wire:loading.attr="disabled"
                                            dusk="lab-remove-exam"
                                            class="flex-shrink-0 text-red-400 hover:text-red-600 dark:hover:text-red-300 transition p-1"
                                            title="Eliminar"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 dark:text-zinc-500 mb-4">Sin exámenes aún.</p>
                    @endif

                    @if (! $finalized)
                        <div
                            class="bg-gray-50 dark:bg-zinc-800/60 rounded-xl p-4 border border-gray-200 dark:border-zinc-700"
                        >
                            <p
                                class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3"
                            >
                                Agregar Examen
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <input
                                        wire:model="newExamName"
                                        type="text"
                                        placeholder="Nombre del examen *"
                                        dusk="lab-exam-name"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                    @error('newExamName')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <input
                                        wire:model="newIndications"
                                        type="text"
                                        placeholder="Indicaciones (opcional)"
                                        dusk="lab-indications"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button
                                    wire:click="addItem"
                                    wire:loading.attr="disabled"
                                    dusk="lab-add-exam-btn"
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white dark:bg-zinc-700 border border-orange-300 dark:border-orange-700 text-orange-700 dark:text-orange-300 text-sm font-medium hover:bg-orange-50 dark:hover:bg-orange-900/30 transition disabled:opacity-50"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                    Agregar
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
