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

    /** @var array<int, array{id: string, name: string, description: string|null}> */
    public array $templates = [];

    public ?string $labRequestId = null;
    public ?string $observations = null;
    public string $status = 'pending';

    /** @var array<int, array{id: string, exam_name: string, indications: string|null, result_value: string|null, is_abnormal: bool, result_notes: string|null, result_received_at: string|null, editing_result: bool}> */
    public array $items = [];

    /** @var array<int, array{template_name: string, applied_at: string}> */
    public array $appliedTemplates = [];

    public string $newExamName = '';
    public string $newIndications = '';

    public bool $finalized = false;
    public bool $saved = false;
    public string $errorMessage = '';

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;

        $consultation = Consultation::with('laboratoryRequest.items', 'laboratoryRequest.appliedTemplates')->findOrFail(
            $consultationId,
        );

        $this->templates = LaboratoryTemplate::query()
            ->where('doctor_id', $consultation->doctor_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description'])
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'description' => $t->description])
            ->all();

        $this->finalized =
            $consultation->status instanceof ConsultationStatus
                ? $consultation->status->isFinalized()
                : (string) $consultation->status === ConsultationStatus::FINALIZED;

        $labReq = $consultation->laboratoryRequest;
        if ($labReq) {
            $this->labRequestId = $labReq->id;
            $this->observations = $labReq->observations;
            $this->status = $labReq->status ?? 'pending';
            $this->saved = true;
            $this->loadItems($labReq->items);
            $this->loadAppliedTemplates($labReq->appliedTemplates);
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
                    'result_value' => $item->result_value,
                    'is_abnormal' => (bool) $item->is_abnormal,
                    'result_notes' => $item->result_notes,
                    'result_received_at' => $item->result_received_at?->format('d/m/Y H:i'),
                    'editing_result' => false,
                ],
            )
            ->values()
            ->all();
    }

    /** @param \Illuminate\Support\Collection<int, \App\Models\LaboratoryAppliedTemplate> $applied */
    private function loadAppliedTemplates(\Illuminate\Support\Collection $applied): void
    {
        $this->appliedTemplates = $applied
            ->map(
                fn ($a) => [
                    'template_name' => $a->template_name,
                    'applied_at' => $a->applied_at?->format('H:i'),
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
            'observations' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', 'in:pending,received'],
        ]);

        try {
            $dto = new LaboratoryRequestDTO(observations: $this->observations ?: null, status: $this->status);
            $labReq = app(LaboratoryRequestServiceContract::class)->upsert($this->consultationId, $dto);
            $this->labRequestId = $labReq->id;
            $this->saved = true;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar: ' . $e->getMessage();
        }
    }

    public function applyTemplate(string $templateId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        if (! $this->labRequestId) {
            $this->saveLabRequest();
            if (! $this->labRequestId) {
                return;
            }
        }

        try {
            $labReq = app(LaboratoryRequestServiceContract::class)->applyTemplate($this->labRequestId, $templateId);
            $this->loadItems($labReq->items);
            $this->loadAppliedTemplates($labReq->appliedTemplates);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al aplicar plantilla: ' . $e->getMessage();
        }
    }

    public function startEditingResult(string $itemId): void
    {
        $this->items = array_map(
            fn ($item) => array_merge($item, ['editing_result' => $item['id'] === $itemId]),
            $this->items,
        );
    }

    public function saveResult(string $itemId): void
    {
        $this->errorMessage = '';

        $index = array_search($itemId, array_column($this->items, 'id'));
        if ($index === false) {
            return;
        }

        $item = $this->items[$index];

        try {
            $updated = app(LaboratoryRequestItemServiceContract::class)->updateResult(
                $itemId,
                $item['result_value'] ?: null,
                (bool) $item['is_abnormal'],
                $item['result_notes'] ?: null,
            );

            $this->items[$index]['result_value'] = $updated->result_value;
            $this->items[$index]['is_abnormal'] = (bool) $updated->is_abnormal;
            $this->items[$index]['result_notes'] = $updated->result_notes;
            $this->items[$index]['result_received_at'] = $updated->result_received_at?->format('d/m/Y H:i');
            $this->items[$index]['editing_result'] = false;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar resultado: ' . $e->getMessage();
        }
    }

    public function cancelEditingResult(string $itemId): void
    {
        $this->items = array_map(fn ($item) => array_merge($item, ['editing_result' => false]), $this->items);
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
            ['newExamName.required' => 'El nombre del examen es obligatorio.'],
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
                'result_value' => null,
                'is_abnormal' => false,
                'result_notes' => null,
                'result_received_at' => null,
                'editing_result' => false,
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
            <div class="flex items-center gap-2">
                @if ($finalized)
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400"
                    >
                        Finalizada
                    </span>
                @elseif ($saved)
                    @if ($status === 'received')
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300"
                        >
                            Resultados recibidos
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300"
                        >
                            Pendiente
                        </span>
                    @endif
                @else
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300"
                    >
                        Sin datos
                    </span>
                @endif
                <span
                    wire:loading
                    wire:target="saveLabRequest,applyTemplate,saveResult"
                    class="text-xs text-orange-400"
                >
                    Guardando…
                </span>
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

            {{-- Estado y observaciones --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                    >
                        Estado
                    </label>
                    <select
                        wire:model="status"
                        wire:change="saveLabRequest"
                        dusk="lab-status"
                        @disabled($finalized)
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <option value="pending">Pendiente (por pedir)</option>
                        <option value="received">Resultados recibidos</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                    >
                        Observaciones / Indicaciones generales
                    </label>
                    <textarea
                        wire:model="observations"
                        wire:change="saveLabRequest"
                        rows="2"
                        dusk="lab-observations"
                        @disabled($finalized)
                        placeholder="Indicaciones especiales para el laboratorio..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm resize-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    ></textarea>
                </div>
            </div>

            {{-- Plantillas disponibles --}}
            @if (! $finalized && count($templates) > 0)
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                        Plantillas — click para aplicar
                    </p>
                    @if (count($appliedTemplates) > 0)
                        <p class="text-xs text-gray-400 dark:text-zinc-500 mb-3">
                            Aplicadas:
                            @foreach ($appliedTemplates as $applied)
                                <span
                                    class="inline-flex items-center gap-1 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 rounded px-1.5 py-0.5 text-xs mr-1"
                                >
                                    {{ $applied['template_name'] }}
                                    <span class="opacity-60">{{ $applied['applied_at'] }}</span>
                                </span>
                            @endforeach
                        </p>
                    @endif

                    <div class="flex flex-wrap gap-2" dusk="lab-templates-panel">
                        @foreach ($templates as $t)
                            <button
                                wire:click="applyTemplate('{{ $t['id'] }}')"
                                wire:loading.attr="disabled"
                                dusk="lab-apply-template"
                                title="{{ $t['description'] }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-orange-300 dark:border-orange-700 bg-white dark:bg-zinc-800 text-orange-700 dark:text-orange-300 text-sm font-medium hover:bg-orange-50 dark:hover:bg-orange-900/20 transition disabled:opacity-40"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                {{ $t['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Exámenes solicitados --}}
            <div class="border-t border-gray-100 dark:border-zinc-800 pt-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                    Exámenes Solicitados
                    @if (count($items) > 0)
                        <span class="ml-1 text-xs font-normal text-gray-400">({{ count($items) }})</span>
                    @endif
                </h3>

                @if (count($items) > 0)
                    <div class="space-y-2 mb-4">
                        @foreach ($items as $index => $item)
                            <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg px-4 py-3" dusk="lab-exam-item">
                                @if ($item['editing_result'])
                                    {{-- Modo ingreso de resultado --}}
                                    <div class="space-y-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                            {{ $item['exam_name'] }}
                                        </p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <input
                                                wire:model="items.{{ $index }}.result_value"
                                                type="text"
                                                placeholder="Valor del resultado"
                                                class="w-full px-2.5 py-1.5 border border-orange-400 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-orange-500"
                                            />
                                            <input
                                                wire:model="items.{{ $index }}.result_notes"
                                                type="text"
                                                placeholder="Notas / Unidades"
                                                class="w-full px-2.5 py-1.5 border border-gray-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-orange-500"
                                            />
                                        </div>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                wire:model="items.{{ $index }}.is_abnormal"
                                                class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                            />
                                            <span class="text-sm text-red-600 dark:text-red-400 font-medium">
                                                Resultado anormal
                                            </span>
                                        </label>
                                        <div class="flex justify-end gap-2 pt-1">
                                            <button
                                                wire:click="cancelEditingResult('{{ $item['id'] }}')"
                                                class="px-3 py-1 text-xs rounded-md border border-gray-300 dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition"
                                            >
                                                Cancelar
                                            </button>
                                            <button
                                                wire:click="saveResult('{{ $item['id'] }}')"
                                                class="px-3 py-1 text-xs rounded-md bg-orange-600 text-white hover:bg-orange-700 transition"
                                            >
                                                Guardar resultado
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    {{-- Vista normal --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $item['exam_name'] }}
                                                </p>
                                                @if ($item['is_abnormal'])
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400"
                                                    >
                                                        Anormal
                                                    </span>
                                                @elseif ($item['result_value'])
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400"
                                                    >
                                                        Normal
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($item['indications'])
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ $item['indications'] }}
                                                </p>
                                            @endif

                                            @if ($item['result_value'])
                                                <p class="text-xs text-gray-700 dark:text-gray-300 mt-1 font-medium">
                                                    Resultado: {{ $item['result_value'] }}
                                                    @if ($item['result_notes'])
                                                            · {{ $item['result_notes'] }}
                                                    @endif
                                                </p>
                                                @if ($item['result_received_at'])
                                                    <p class="text-xs text-gray-400 dark:text-zinc-500">
                                                        Recibido: {{ $item['result_received_at'] }}
                                                    </p>
                                                @endif
                                            @else
                                                <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5 italic">
                                                    Sin resultado
                                                </p>
                                            @endif
                                        </div>
                                        @if (! $finalized)
                                            <div class="flex-shrink-0 flex items-center gap-1">
                                                <button
                                                    wire:click="startEditingResult('{{ $item['id'] }}')"
                                                    dusk="lab-enter-result"
                                                    class="text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 transition p-1"
                                                    title="Ingresar resultado"
                                                >
                                                    <svg
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                                        />
                                                    </svg>
                                                </button>
                                                <button
                                                    wire:click="removeItem('{{ $item['id'] }}')"
                                                    wire:loading.attr="disabled"
                                                    dusk="lab-remove-exam"
                                                    class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition p-1"
                                                    title="Eliminar"
                                                >
                                                    <svg
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 dark:text-zinc-500 mb-4">
                        Sin exámenes aún. Aplica una plantilla o agrega uno manualmente.
                    </p>
                @endif

                {{-- Formulario agregar examen libre --}}
                @if (! $finalized)
                    @if ($labRequestId)
                        <div
                            class="bg-gray-50 dark:bg-zinc-800/60 rounded-xl p-4 border border-gray-200 dark:border-zinc-700"
                        >
                            <p
                                class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3"
                            >
                                Agregar Examen Libre
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
                    @else
                        <p class="text-xs text-gray-400 dark:text-zinc-500 italic">
                            Escribe una observación o aplica una plantilla para habilitar la edición de exámenes.
                        </p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
