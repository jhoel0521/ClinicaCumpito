<?php

use App\Contracts\LaboratoryRequestItemServiceContract;
use App\Contracts\LaboratoryRequestServiceContract;
use App\DTOs\LaboratoryRequestDTO;
use App\DTOs\LaboratoryRequestItemDTO;
use App\Models\Consultation;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\ValueObjects\ConsultationStatus;
use Livewire\Component;

new class extends Component {
    public string $consultationId;
    public bool $finalized = false;
    public string $errorMessage = '';

    public ?string $labRequestId = null;
    public string $status = 'pending';
    public string $observations = '';

    /**
     * Items grouped by exam: ['Hemograma' => [['id','parameter_name','indications','result_value','is_abnormal','result_notes','result_received_at','editing_result']]]
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    public array $groupedItems = [];

    // ── Exam selector state ──
    public bool $showSelector = false;
    public ?string $selectedCategoryId = null;
    public ?string $selectedExamId = null;
    public string $selectedExamName = '';

    /**
     * Parameters of selected exam with checked state
     *
     * @var array<int, array{name: string, checked: bool}>
     */
    public array $selectorParameters = [];

    // Custom parameter
    public string $customParamName = '';

    // Indications for this batch
    public string $selectorIndications = '';

    /**
     * Categories with exams (loaded once)
     *
     * @var array<int, array{id: string, name: string, exams: array<int, array{id: string, name: string, parameters: array<int, string>}>}>
     */
    public array $categories = [];

    // Result editing
    public ?string $editingResultItemId = null;
    public string $editResultValue = '';
    public bool $editResultAbnormal = false;
    public string $editResultNotes = '';

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;

        $consultation = Consultation::findOrFail($consultationId);

        $this->finalized =
            $consultation->status instanceof ConsultationStatus
                ? $consultation->status->isFinalized()
                : (string) $consultation->status === ConsultationStatus::FINALIZED;

        // Load catalog for selector
        $this->categories = LaboratoryCategory::with('exams.parameters')
            ->orderBy('name')
            ->get()
            ->map(
                fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'exams' => $cat->exams
                        ->map(
                            fn ($e) => [
                                'id' => $e->id,
                                'name' => $e->name,
                                'parameters' => $e->parameters->pluck('name')->all(),
                            ],
                        )
                        ->values()
                        ->all(),
                ],
            )
            ->values()
            ->all();

        // Load existing lab request
        $labReq = LaboratoryRequest::with('items')
            ->where('consultation_id', $consultationId)
            ->first();

        if ($labReq) {
            $this->labRequestId = $labReq->id;
            $this->observations = $labReq->observations ?? '';
            $this->status = $labReq->status ?? 'pending';
            $this->loadGroupedItems($labReq->items);
        }
    }

    /** @param \Illuminate\Support\Collection<int, \App\Models\LaboratoryRequestItem> $items */
    private function loadGroupedItems(\Illuminate\Support\Collection $items): void
    {
        $grouped = [];
        foreach ($items as $item) {
            $examName = $item->exam_name;
            if (! isset($grouped[$examName])) {
                $grouped[$examName] = [];
            }
            $grouped[$examName][] = [
                'id' => $item->id,
                'parameter_name' => $item->parameter_name,
                'indications' => $item->indications,
                'result_value' => $item->result_value,
                'is_abnormal' => (bool) $item->is_abnormal,
                'result_notes' => $item->result_notes,
                'result_received_at' => $item->result_received_at?->format('d/m/Y H:i'),
                'editing_result' => false,
            ];
        }
        $this->groupedItems = $grouped;
    }

    private function ensureLabRequest(): void
    {
        if ($this->labRequestId) {
            return;
        }

        $dto = new LaboratoryRequestDTO(observations: null, status: 'pending');
        $req = app(LaboratoryRequestServiceContract::class)->createForConsultation($this->consultationId, $dto);
        $this->labRequestId = $req->id;
    }

    // ── Selector ──

    public function openSelector(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->showSelector = true;
        $this->selectedCategoryId = null;
        $this->selectedExamId = null;
        $this->selectedExamName = '';
        $this->selectorParameters = [];
        $this->selectorIndications = '';
        $this->customParamName = '';
    }

    public function closeSelector(): void
    {
        $this->showSelector = false;
    }

    public function selectCategory(string $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
        $this->selectedExamId = null;
        $this->selectedExamName = '';
        $this->selectorParameters = [];
    }

    public function selectExam(string $examId): void
    {
        $this->selectedExamId = $examId;
        $this->selectorParameters = [];

        foreach ($this->categories as $cat) {
            foreach ($cat['exams'] as $exam) {
                if ($exam['id'] === $examId) {
                    $this->selectedExamName = $exam['name'];
                    $this->selectorParameters = array_map(
                        fn ($p) => ['name' => $p, 'checked' => true],
                        $exam['parameters'],
                    );
                    break 2;
                }
            }
        }

        $this->customParamName = '';
    }

    public function addCustomParam(): void
    {
        $name = trim($this->customParamName);
        if ($name === '') {
            return;
        }

        $this->selectorParameters[] = ['name' => $name, 'checked' => true];
        $this->customParamName = '';
    }

    public function addSelectedToRequest(): void
    {
        if ($this->finalized || ! $this->selectedExamId) {
            return;
        }

        $this->errorMessage = '';

        $selected = array_filter($this->selectorParameters, fn ($p) => $p['checked']);
        if (empty($selected)) {
            $this->errorMessage = 'Selecciona al menos un parámetro.';

            return;
        }

        try {
            $this->ensureLabRequest();

            foreach ($selected as $param) {
                $dto = new LaboratoryRequestItemDTO(
                    exam_name: $this->selectedExamName,
                    parameter_name: $param['name'],
                    indications: trim($this->selectorIndications) !== '' ? trim($this->selectorIndications) : null,
                );
                $item = app(LaboratoryRequestItemServiceContract::class)->create($this->labRequestId, $dto);

                if (! isset($this->groupedItems[$this->selectedExamName])) {
                    $this->groupedItems[$this->selectedExamName] = [];
                }

                $this->groupedItems[$this->selectedExamName][] = [
                    'id' => $item->id,
                    'parameter_name' => $item->parameter_name,
                    'indications' => $item->indications,
                    'result_value' => null,
                    'is_abnormal' => false,
                    'result_notes' => null,
                    'result_received_at' => null,
                    'editing_result' => false,
                ];
            }

            $this->closeSelector();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al agregar exámenes: ' . $e->getMessage();
        }
    }

    // ── Remove item / exam group ──

    public function removeItem(string $itemId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            app(LaboratoryRequestItemServiceContract::class)->delete($itemId);

            foreach ($this->groupedItems as $examName => &$items) {
                $items = array_values(array_filter($items, fn ($i) => $i['id'] !== $itemId));
                if (empty($items)) {
                    unset($this->groupedItems[$examName]);
                    break;
                }
            }
            unset($items);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar: ' . $e->getMessage();
        }
    }

    // ── Results ──

    public function startEditingResult(string $itemId): void
    {
        $this->editingResultItemId = $itemId;

        foreach ($this->groupedItems as $items) {
            foreach ($items as $item) {
                if ($item['id'] === $itemId) {
                    $this->editResultValue = $item['result_value'] ?? '';
                    $this->editResultAbnormal = (bool) $item['is_abnormal'];
                    $this->editResultNotes = $item['result_notes'] ?? '';

                    return;
                }
            }
        }
    }

    public function saveResult(string $itemId): void
    {
        $this->errorMessage = '';

        try {
            $item = app(LaboratoryRequestItemServiceContract::class)->updateResult(
                $itemId,
                $this->editResultValue !== '' ? $this->editResultValue : null,
                $this->editResultAbnormal,
                $this->editResultNotes !== '' ? $this->editResultNotes : null,
            );

            foreach ($this->groupedItems as $examName => &$items) {
                foreach ($items as &$i) {
                    if ($i['id'] === $itemId) {
                        $i['result_value'] = $item->result_value;
                        $i['is_abnormal'] = (bool) $item->is_abnormal;
                        $i['result_notes'] = $item->result_notes;
                        $i['result_received_at'] = $item->result_received_at?->format('d/m/Y H:i');
                        $i['editing_result'] = false;
                        break 2;
                    }
                }
            }
            unset($items, $i);

            $this->editingResultItemId = null;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar resultado: ' . $e->getMessage();
        }
    }

    public function cancelEditingResult(): void
    {
        $this->editingResultItemId = null;
    }

    public function saveStatus(): void
    {
        if (! $this->labRequestId) {
            return;
        }

        try {
            $dto = new LaboratoryRequestDTO(
                observations: $this->observations !== '' ? $this->observations : null,
                status: $this->status,
            );
            app(LaboratoryRequestServiceContract::class)->update($this->labRequestId, $dto);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar: ' . $e->getMessage();
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
                <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/40 flex items-center justify-center">
                    <svg
                        class="w-4 h-4 text-sky-600 dark:text-sky-400"
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
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Solicitud de Laboratorio</h2>
                    @if ($labRequestId)
                        <div class="flex items-center gap-2 mt-0.5">
                            <select
                                wire:model.change="status"
                                wire:change="saveStatus"
                                @disabled($finalized)
                                class="text-xs rounded-full px-2 py-0.5 border font-medium transition focus:ring-1 focus:ring-sky-500 disabled:opacity-50 @if($status === 'pending') bg-amber-50 dark:bg-amber-900/20 border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 @else bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700 text-green-700 dark:text-green-300 @endif"
                            >
                                <option value="pending">Pendiente</option>
                                <option value="received">Resultados recibidos</option>
                            </select>
                        </div>
                    @endif
                </div>
            </div>
            @if (! $finalized)
                <button
                    wire:click="openSelector"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium transition"
                >
                    <flux:icon.plus class="size-4" />
                    Agregar examen
                </button>
            @else
                <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400"
                >
                    Finalizada
                </span>
            @endif
        </div>

        <div class="p-6 space-y-4">
            @if ($errorMessage)
                <div
                    class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300"
                >
                    {{ $errorMessage }}
                </div>
            @endif

            {{-- ── Selector de examen ── --}}
            @if ($showSelector)
                <div
                    class="rounded-xl border border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-900/10 overflow-hidden"
                >
                    <div
                        class="flex items-center justify-between px-4 py-3 border-b border-sky-200 dark:border-sky-800"
                    >
                        <p class="text-sm font-semibold text-sky-700 dark:text-sky-400">
                            Seleccionar examen del catálogo
                        </p>
                        <button
                            wire:click="closeSelector"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                        >
                            <flux:icon.x-mark class="size-4" />
                        </button>
                    </div>

                    <div
                        class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-sky-200 dark:divide-sky-800"
                        style="min-height: 220px"
                    >
                        {{-- Columna 1: Categorías --}}
                        <div class="p-3 overflow-y-auto max-h-64">
                            <p
                                class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2"
                            >
                                Categoría
                            </p>
                            @foreach ($categories as $cat)
                                <button
                                    wire:click="selectCategory('{{ $cat['id'] }}')"
                                    @class([
                                        'w-full text-left px-3 py-2 rounded-lg text-sm transition mb-1',
                                        'bg-sky-600 text-white' => $selectedCategoryId === $cat['id'],
                                        'hover:bg-sky-100 dark:hover:bg-sky-900/30 text-gray-700 dark:text-gray-300' => $selectedCategoryId !== $cat['id'],
                                    ])
                                >
                                    {{ $cat['name'] }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Columna 2: Exámenes --}}
                        <div class="p-3 overflow-y-auto max-h-64">
                            @if ($selectedCategoryId)
                                <p
                                    class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2"
                                >
                                    Examen
                                </p>
                                @foreach ($categories as $cat)
                                    @if ($cat['id'] === $selectedCategoryId)
                                        @foreach ($cat['exams'] as $exam)
                                            <button
                                                wire:click="selectExam('{{ $exam['id'] }}')"
                                                @class([
                                                    'w-full text-left px-3 py-2 rounded-lg text-sm transition mb-1',
                                                    'bg-sky-600 text-white' => $selectedExamId === $exam['id'],
                                                    'hover:bg-sky-100 dark:hover:bg-sky-900/30 text-gray-700 dark:text-gray-300' => $selectedExamId !== $exam['id'],
                                                ])
                                            >
                                                {{ $exam['name'] }}
                                            </button>
                                        @endforeach
                                    @endif
                                @endforeach
                            @else
                                <p class="text-xs text-gray-400 dark:text-zinc-500 italic p-2">
                                    ← Selecciona una categoría
                                </p>
                            @endif
                        </div>

                        {{-- Columna 3: Parámetros --}}
                        <div class="p-3 overflow-y-auto max-h-64">
                            @if ($selectedExamId)
                                <p
                                    class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2"
                                >
                                    Parámetros — {{ $selectedExamName }}
                                </p>
                                <div class="space-y-1.5 mb-3">
                                    @foreach ($selectorParameters as $pIdx => $param)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input
                                                type="checkbox"
                                                wire:model="selectorParameters.{{ $pIdx }}.checked"
                                                class="rounded border-gray-300 dark:border-zinc-600 text-sky-600 focus:ring-sky-500"
                                            />
                                            <span
                                                class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-sky-700 dark:group-hover:text-sky-400 transition"
                                            >
                                                {{ $param['name'] }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                {{-- Parámetro personalizado --}}
                                <div class="flex gap-1.5 mt-2">
                                    <input
                                        wire:model="customParamName"
                                        wire:keydown.enter="addCustomParam"
                                        type="text"
                                        placeholder="Parámetro personalizado..."
                                        class="flex-1 px-2 py-1 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-xs focus:ring-1 focus:ring-sky-500"
                                    />
                                    <button
                                        wire:click="addCustomParam"
                                        class="px-2 py-1 rounded bg-gray-200 dark:bg-zinc-700 hover:bg-gray-300 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-300 text-xs transition"
                                    >
                                        +
                                    </button>
                                </div>
                            @elseif ($selectedCategoryId)
                                <p class="text-xs text-gray-400 dark:text-zinc-500 italic p-2">
                                    ← Selecciona un examen
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Footer: indicaciones + confirmar --}}
                    @if ($selectedExamId)
                        <div class="border-t border-sky-200 dark:border-sky-800 px-4 py-3 flex items-center gap-3">
                            <input
                                wire:model="selectorIndications"
                                type="text"
                                placeholder="Indicaciones para el laboratorio (opcional)"
                                class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-sky-500"
                            />
                            <button
                                wire:click="addSelectedToRequest"
                                wire:loading.attr="disabled"
                                class="px-4 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium transition disabled:opacity-50"
                            >
                                Agregar al pedido
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ── Exámenes solicitados ── --}}
            @if (count($groupedItems) > 0)
                <div class="space-y-3">
                    @foreach ($groupedItems as $examName => $items)
                        <div class="rounded-xl border border-gray-200 dark:border-zinc-700 overflow-hidden">
                            {{-- Cabecera del examen --}}
                            <div
                                class="flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700"
                            >
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $examName }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-zinc-500">
                                    {{ count($items) }} parámetro(s)
                                </span>
                            </div>

                            {{-- Parámetros --}}
                            <div class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @foreach ($items as $item)
                                    <div class="px-4 py-2.5 bg-white dark:bg-zinc-900" dusk="lab-item">
                                        @if ($editingResultItemId === $item['id'])
                                            {{-- Editor de resultado --}}
                                            <div class="space-y-2">
                                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                                    {{ $item['parameter_name'] ?? $examName }}
                                                </p>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                    <input
                                                        wire:model="editResultValue"
                                                        type="text"
                                                        placeholder="Valor del resultado"
                                                        class="px-2.5 py-1.5 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-sky-500"
                                                    />
                                                    <input
                                                        wire:model="editResultNotes"
                                                        type="text"
                                                        placeholder="Notas adicionales"
                                                        class="px-2.5 py-1.5 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-sky-500"
                                                    />
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <label
                                                        class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400 cursor-pointer"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            wire:model="editResultAbnormal"
                                                            class="rounded border-gray-300 text-red-500 focus:ring-red-400"
                                                        />
                                                        Resultado anormal
                                                    </label>
                                                    <div class="flex gap-2">
                                                        <button
                                                            wire:click="saveResult('{{ $item['id'] }}')"
                                                            class="px-3 py-1 text-xs rounded bg-sky-600 hover:bg-sky-700 text-white transition"
                                                        >
                                                            Guardar
                                                        </button>
                                                        <button
                                                            wire:click="cancelEditingResult"
                                                            class="px-3 py-1 text-xs rounded border border-gray-300 dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition"
                                                        >
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="text-sm text-gray-800 dark:text-gray-200">
                                                            {{ $item['parameter_name'] ?? '—' }}
                                                        </span>
                                                        @if ($item['result_value'])
                                                            <span
                                                                @class([
                                                                    'inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium',
                                                                    'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' => $item['is_abnormal'],
                                                                    'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' => ! $item['is_abnormal'],
                                                                ])
                                                            >
                                                                @if ($item['is_abnormal'])
                                                                    <flux:icon.exclamation-triangle class="size-3" />
                                                                @else
                                                                    <flux:icon.check class="size-3" />
                                                                @endif
                                                                {{ $item['result_value'] }}
                                                            </span>
                                                        @endif

                                                        @if ($item['result_notes'])
                                                            <span
                                                                class="text-xs text-gray-400 dark:text-zinc-500 italic"
                                                            >
                                                                {{ $item['result_notes'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if ($item['indications'])
                                                        <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5">
                                                            Indicación: {{ $item['indications'] }}
                                                        </p>
                                                    @endif

                                                    @if ($item['result_received_at'])
                                                        <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5">
                                                            Recibido: {{ $item['result_received_at'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-1 flex-shrink-0">
                                                    @if (! $finalized)
                                                        <button
                                                            wire:click="startEditingResult('{{ $item['id'] }}')"
                                                            class="text-sky-400 hover:text-sky-600 dark:hover:text-sky-300 transition p-0.5"
                                                            title="{{ $item['result_value'] ? 'Editar resultado' : 'Registrar resultado' }}"
                                                        >
                                                            <flux:icon.clipboard-document-check class="size-4" />
                                                        </button>
                                                        <button
                                                            wire:click="removeItem('{{ $item['id'] }}')"
                                                            wire:loading.attr="disabled"
                                                            class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition p-0.5"
                                                            title="Eliminar"
                                                            dusk="lab-remove-item"
                                                        >
                                                            <flux:icon.trash class="size-4" />
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Observaciones --}}
                @if ($labRequestId)
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                        >
                            Observaciones / Instrucciones generales
                        </label>
                        <textarea
                            wire:model="observations"
                            wire:change="saveStatus"
                            rows="2"
                            @disabled($finalized)
                            placeholder="Instrucciones generales para el laboratorio..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm resize-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 disabled:opacity-50"
                        ></textarea>
                    </div>
                @endif
            @else
                <div class="text-center py-10 text-gray-400 dark:text-zinc-500">
                    <svg
                        class="w-10 h-10 mx-auto mb-2 opacity-30"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                        />
                    </svg>
                    <p class="text-sm">Sin exámenes solicitados.</p>
                    @if (! $finalized)
                        <p class="text-xs mt-1">Usa "Agregar examen" para seleccionar del catálogo.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
