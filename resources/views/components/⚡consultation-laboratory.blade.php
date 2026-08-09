<?php

use App\Contracts\LaboratoryItemResultServiceContract;
use App\Contracts\LaboratoryRequestItemServiceContract;
use App\Contracts\LaboratoryRequestServiceContract;
use App\DTOs\LaboratoryRequestDTO;
use App\DTOs\LaboratoryRequestItemDTO;
use App\Models\Consultation;
use App\Models\LaboratoryAttachment;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryRequest;
use App\ValueObjects\ConsultationStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $consultationId;
    public bool $finalized = false;
    public string $errorMessage = '';

    /**
     * All lab orders for this consultation.
     * Each entry: [id, examName, status, presumptive_diagnosis, observations, items, orderAttachments]
     * items: [[id, parameter_name, results[], attachments[]]]
     * orderAttachments: [[id, original_name, mime_type, url, is_image, is_pdf]]
     *
     * @var array<int, array<string, mixed>>
     */
    public array $labRequests = [];

    /**
     * Pendientes de otras consultas del mismo paciente.
     * @var array<int, array<string, mixed>>
     */
    public array $pendingPreviousLabRequests = [];

    /**
     * Exam catalog for the selector.
     *
     * @var array<int, array{id: string, name: string, exams: array<int, array{id: string, name: string, parameters: array<int, string>}>}>
     */
    public array $categories = [];

    // ── New lab form ──
    public bool $showNewForm = false;
    public string $newPresumptiveDiagnosis = '';
    public string $newObservations = '';

    // ── Exam selector state (used only in the new-lab form) ──
    public ?string $selectedCategoryId = null;
    public ?string $selectedExamId = null;
    public string $selectedExamName = '';

    /**
     * @var array<int, array{name: string, checked: bool}>
     */
    public array $selectorParameters = [];

    public string $customParamName = '';

    // ── Add result state ──
    public array $newResults = [];

    // ── Attachment upload state ──
    /** @var mixed */
    public $newAttachmentFile = null;

    public ?string $attachingToItemId = null; // null = order-level, uuid = item-level
    public ?string $attachingToRequestId = null; // which order receives an order-level attachment

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;

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

        $this->reload();
    }

    private function reload(): void
    {
        $consultation = Consultation::findOrFail($this->consultationId);

        $this->finalized =
            $consultation->status instanceof ConsultationStatus
                ? $consultation->status->isFinalized()
                : (string) $consultation->status === ConsultationStatus::FINALIZED;

        $this->labRequests = LaboratoryRequest::with(['items.results', 'items.attachments', 'attachments'])
            ->where('consultation_id', $this->consultationId)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($r) => $this->mapRequest($r))
            ->values()
            ->all();

        $this->pendingPreviousLabRequests = LaboratoryRequest::with([
            'items.results',
            'items.attachments',
            'attachments',
            'consultation',
        ])
            ->whereHas('consultation', fn ($q) => $q->where('patient_id', $consultation->patient_id))
            ->where('consultation_id', '!=', $this->consultationId)
            ->where('status', 'pending')
            // Solo las que siguen sin atender: las que ya tienen resultados
            // registrados (aunque no se hayan marcado como recibidas) no
            // deben volver a aparecer en cada consulta nueva.
            ->whereDoesntHave('items.results')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(
                fn ($r) => array_merge($this->mapRequest($r), [
                    'consultation_date' => $r->consultation->consultation_date->format('d/m/Y'),
                ]),
            )
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function mapRequest(LaboratoryRequest $r): array
    {
        $examName = '';
        $items = [];

        foreach ($r->items as $item) {
            if ($examName === '') {
                $examName = $item->exam_name;
            }
            $items[] = [
                'id' => $item->id,
                'parameter_name' => $item->parameter_name,
                'results' => $item->results
                    ->map(
                        fn ($res) => [
                            'id' => $res->id,
                            'consultation_id' => $res->consultation_id,
                            'value' => $res->value,
                            'report_text' => $res->report_text,
                            'is_abnormal' => (bool) $res->is_abnormal,
                        ],
                    )
                    ->all(),
                'attachments' => $item->attachments
                    ->map(
                        fn ($a) => [
                            'id' => $a->id,
                            'original_name' => $a->original_name,
                            'mime_type' => $a->mime_type,
                            'url' => $a->url(),
                            'is_image' => $a->isImage(),
                            'is_pdf' => $a->isPdf(),
                        ],
                    )
                    ->all(),
            ];
        }

        return [
            'id' => $r->id,
            'examName' => $examName !== '' ? $examName : 'Sin examen',
            'status' => $r->status ?? 'pending',
            'presumptive_diagnosis' => $r->presumptive_diagnosis ?? '',
            'observations' => $r->observations ?? '',
            'items' => $items,
            'orderAttachments' => $r->attachments
                ->map(
                    fn ($a) => [
                        'id' => $a->id,
                        'original_name' => $a->original_name,
                        'mime_type' => $a->mime_type,
                        'url' => $a->url(),
                        'is_image' => $a->isImage(),
                        'is_pdf' => $a->isPdf(),
                    ],
                )
                ->all(),
        ];
    }

    // ── New lab order (atomic: request + items) ──

    public function submitNewLabOrder(): void
    {
        if ($this->finalized || ! $this->selectedExamId) {
            return;
        }

        $this->errorMessage = '';

        try {
            $dto = new LaboratoryRequestDTO(
                observations: trim($this->newObservations) !== '' ? trim($this->newObservations) : null,
                status: 'pending',
                presumptive_diagnosis: trim($this->newPresumptiveDiagnosis) !== ''
                    ? trim($this->newPresumptiveDiagnosis)
                    : null,
            );

            $req = app(LaboratoryRequestServiceContract::class)->createForConsultation($this->consultationId, $dto);

            $selected = array_filter($this->selectorParameters, fn ($p) => $p['checked']);

            if (! empty($selected)) {
                foreach ($selected as $param) {
                    $itemDto = new LaboratoryRequestItemDTO(
                        exam_name: $this->selectedExamName,
                        parameter_name: $param['name'],
                    );
                    app(LaboratoryRequestItemServiceContract::class)->create($req->id, $itemDto);
                }
            } else {
                $itemDto = new LaboratoryRequestItemDTO(exam_name: $this->selectedExamName, parameter_name: null);
                app(LaboratoryRequestItemServiceContract::class)->create($req->id, $itemDto);
            }

            $this->cancelNewLabOrder();
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al agregar laboratorio: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }

    public function cancelNewLabOrder(): void
    {
        $this->showNewForm = false;
        $this->newPresumptiveDiagnosis = '';
        $this->newObservations = '';
        $this->selectedCategoryId = null;
        $this->selectedExamId = null;
        $this->selectedExamName = '';
        $this->selectorParameters = [];
        $this->customParamName = '';
    }

    public function deleteLabRequest(string $requestId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            $req = LaboratoryRequest::with(['items.attachments', 'attachments'])->find($requestId);
            if ($req) {
                foreach ($req->attachments as $att) {
                    Storage::disk('public')->delete($att->file_path);
                }
                foreach ($req->items as $item) {
                    foreach ($item->attachments as $att) {
                        Storage::disk('public')->delete($att->file_path);
                    }
                }
            }

            app(LaboratoryRequestServiceContract::class)->delete($requestId);

            $this->labRequests = array_values(array_filter($this->labRequests, fn ($r) => $r['id'] !== $requestId));

            if ($this->attachingToRequestId === $requestId) {
                $this->attachingToRequestId = null;
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }

    public function saveField(string $requestId, string $field, string $value): void
    {
        $allowed = ['status', 'presumptive_diagnosis', 'observations'];
        if (! in_array($field, $allowed, true)) {
            return;
        }

        // Only allow status change if finalized (to allow loading results)
        // presumptive_diagnosis and observations remain locked if finalized
        if ($this->finalized && $field !== 'status') {
            return;
        }

        $rIndex = array_search($requestId, array_column($this->labRequests, 'id'));
        $isPrevious = false;

        if ($rIndex === false) {
            $rIndex = array_search($requestId, array_column($this->pendingPreviousLabRequests, 'id'));
            if ($rIndex === false) {
                return;
            }
            $isPrevious = true;
        }

        if ($isPrevious) {
            $this->pendingPreviousLabRequests[$rIndex][$field] = $value;
            $r = $this->pendingPreviousLabRequests[$rIndex];
        } else {
            $this->labRequests[$rIndex][$field] = $value;
            $r = $this->labRequests[$rIndex];
        }

        $this->errorMessage = '';

        try {
            $dto = new LaboratoryRequestDTO(
                observations: $r['observations'] !== '' ? $r['observations'] : null,
                status: $r['status'],
                presumptive_diagnosis: $r['presumptive_diagnosis'] !== '' ? $r['presumptive_diagnosis'] : null,
            );
            app(LaboratoryRequestServiceContract::class)->update($requestId, $dto);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }

    // ── Exam selector (used in the new-lab form) ──

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
                    // Ningún parámetro seleccionado por defecto: el usuario marca solo lo que necesita
                    $this->selectorParameters = array_map(
                        fn ($p) => ['name' => $p, 'checked' => false],
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

    /** Marca o desmarca todos los parámetros del selector de una vez. */
    public function setAllParamsChecked(bool $checked): void
    {
        foreach ($this->selectorParameters as $idx => $param) {
            $this->selectorParameters[$idx]['checked'] = $checked;
        }
    }

    // ── Remove item ──

    public function removeItem(string $itemId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            app(LaboratoryRequestItemServiceContract::class)->delete($itemId);
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }

    // ── Results ──

    public function saveAllResults(string $requestId): void
    {
        $this->errorMessage = '';

        try {
            $rIndex = array_search($requestId, array_column($this->labRequests, 'id'));
            $isPrevious = false;

            if ($rIndex === false) {
                $rIndex = array_search($requestId, array_column($this->pendingPreviousLabRequests, 'id'));
                if ($rIndex === false) {
                    return;
                }
                $isPrevious = true;
            }

            $request = $isPrevious ? $this->pendingPreviousLabRequests[$rIndex] : $this->labRequests[$rIndex];

            $saved = false;
            foreach ($request['items'] as $item) {
                $itemId = $item['id'];
                $data = $this->newResults[$itemId] ?? [];

                if (empty($data['value']) && empty($data['report'])) {
                    continue;
                }

                app(LaboratoryItemResultServiceContract::class)->create(
                    $itemId,
                    [
                        'value' => ! empty($data['value']) ? trim((string) $data['value']) : null,
                        'report_text' => ! empty($data['report']) ? trim((string) $data['report']) : null,
                        'is_abnormal' => ! empty($data['abnormal']),
                    ],
                    $this->consultationId,
                );

                unset($this->newResults[$itemId]);
                $saved = true;
            }

            if ($saved) {
                $this->reload();
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar resultados: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }

    public function deleteResult(string $resultId): void
    {
        $this->errorMessage = '';

        try {
            app(LaboratoryItemResultServiceContract::class)->delete($resultId);
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar resultado: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }

    // ── Attachments ──

    public function openAttachment(string $requestId, ?string $itemId = null): void
    {
        $this->attachingToRequestId = $requestId;
        $this->attachingToItemId = $itemId;
        $this->newAttachmentFile = null;
    }

    public function uploadAttachment(): void
    {
        if (! $this->attachingToRequestId) {
            return;
        }

        $this->errorMessage = '';

        $this->validate([
            'newAttachmentFile' => ['required', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:10240'],
        ]);

        try {
            $file = $this->newAttachmentFile;
            $ext = $file->getClientOriginalExtension();
            $mime = $file->getMimeType() ?? 'application/octet-stream';
            $uuid = (string) Str::uuid();
            $path = "lab-attachments/{$this->attachingToRequestId}/{$uuid}.{$ext}";

            $file->storeAs('lab-attachments/' . $this->attachingToRequestId, "{$uuid}.{$ext}", 'public');

            LaboratoryAttachment::create([
                'laboratory_request_id' => $this->attachingToItemId === null ? $this->attachingToRequestId : null,
                'laboratory_request_item_id' => $this->attachingToItemId,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
            ]);

            $this->newAttachmentFile = null;
            $this->attachingToItemId = null;
            $this->attachingToRequestId = null;
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al subir archivo: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }

    public function deleteAttachment(string $attachmentId): void
    {
        $this->errorMessage = '';

        try {
            $attachment = LaboratoryAttachment::findOrFail($attachmentId);
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar archivo: ' . $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
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
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Solicitudes de Laboratorio</h2>
                    @if (count($labRequests) > 0)
                        <p class="text-xs text-gray-400 dark:text-zinc-500">{{ count($labRequests) }} solicitud(es)</p>
                    @endif
                </div>
            </div>
            @if (! $finalized)
                <button
                    wire:click="$set('showNewForm', true)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium transition"
                >
                    <flux:icon.plus class="size-4" />
                    Agregar Lab
                </button>
            @else
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400"
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

            {{-- ── Panel: Agregar nueva solicitud de laboratorio ── --}}
            @if ($showNewForm && ! $finalized)
                <div
                    class="rounded-xl border border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-900/10 overflow-hidden"
                >
                    <div
                        class="flex items-center justify-between px-4 py-3 border-b border-sky-200 dark:border-sky-800"
                    >
                        <p class="text-sm font-semibold text-sky-700 dark:text-sky-400">
                            Agregar solicitud de laboratorio
                        </p>
                        <button
                            wire:click="cancelNewLabOrder"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                        >
                            <flux:icon.x-mark class="size-4" />
                        </button>
                    </div>

                    {{-- Selector 3 columnas --}}
                    <div
                        class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-sky-200 dark:divide-sky-800"
                        style="min-height: 200px"
                    >
                        {{-- Columna 1: Categorías --}}
                        <div class="p-3 overflow-y-auto max-h-56">
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
                        <div class="p-3 overflow-y-auto max-h-56">
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
                        <div class="p-3 overflow-y-auto max-h-56">
                            @if ($selectedExamId)
                                <p
                                    class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2"
                                >
                                    Parámetros — {{ $selectedExamName }}
                                    <span class="font-normal text-gray-400 dark:text-zinc-500">(opcional)</span>
                                </p>
                                @if (count($selectorParameters) > 0)
                                    <div class="flex gap-2 mb-2">
                                        <button
                                            wire:click="setAllParamsChecked(true)"
                                            class="text-xs text-sky-600 dark:text-sky-400 hover:underline"
                                        >
                                            Todos
                                        </button>
                                        <span class="text-xs text-gray-300 dark:text-zinc-600">·</span>
                                        <button
                                            wire:click="setAllParamsChecked(false)"
                                            class="text-xs text-sky-600 dark:text-sky-400 hover:underline"
                                        >
                                            Ninguno
                                        </button>
                                    </div>
                                @endif

                                @if (count($selectorParameters) > 0)
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
                                @else
                                    <p class="text-xs text-gray-400 dark:text-zinc-500 italic mb-3">
                                        Sin parámetros definidos — se agregará el examen completo.
                                    </p>
                                @endif

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

                    {{-- Diagnóstico presuntivo + Observaciones + botones --}}
                    <div class="border-t border-sky-200 dark:border-sky-800 px-4 py-4 space-y-3">
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide"
                            >
                                Diagnóstico presuntivo
                            </label>
                            <input
                                wire:model="newPresumptiveDiagnosis"
                                type="text"
                                placeholder="Ej: Posible anemia ferropénica, Diabetes en estudio..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide"
                            >
                                Observaciones
                            </label>
                            <textarea
                                wire:model="newObservations"
                                rows="2"
                                placeholder="Instrucciones para el paciente (ej: Ayunas mínimo 8 horas)..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm resize-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            ></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button
                                wire:click="submitNewLabOrder"
                                wire:loading.attr="disabled"
                                @disabled(! $selectedExamId)
                                class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Agregar
                            </button>
                            <button
                                wire:click="cancelNewLabOrder"
                                class="px-3 py-2 rounded-lg border border-gray-300 dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-800 text-sm transition"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Lista de laboratorios (Actuales y Pendientes Previas) ── --}}
            @forelse (array_merge($pendingPreviousLabRequests, $labRequests) as $labReq)
                <div
                    class="rounded-xl border {{ isset($labReq['consultation_date']) ? 'border-amber-200 dark:border-amber-700/50' : 'border-gray-200 dark:border-zinc-700' }} overflow-hidden"
                >
                    {{-- Encabezado: nombre del examen + estado + eliminar --}}
                    <div
                        class="flex items-center justify-between px-4 py-3 {{ isset($labReq['consultation_date']) ? 'bg-amber-50 dark:bg-amber-900/10 border-b border-amber-200 dark:border-amber-700/50' : 'bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700' }}"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            @if (isset($labReq['consultation_date']))
                                <span
                                    class="shrink-0 text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400 px-2 py-0.5 rounded"
                                >
                                    Previa ({{ $labReq['consultation_date'] }})
                                </span>
                            @endif

                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">
                                {{ $labReq['examName'] }}
                            </span>
                            <select
                                wire:change="saveField('{{ $labReq['id'] }}', 'status', $event.target.value)"
                                class="text-xs rounded-full px-2 py-0.5 border font-medium transition focus:ring-1 focus:ring-sky-500 flex-shrink-0 @if ($labReq['status'] === 'pending') bg-amber-50 dark:bg-amber-900/20 border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 @else bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700 text-green-700 dark:text-green-300 @endif"
                            >
                                <option value="pending" @selected($labReq['status'] === 'pending')>Pendiente</option>
                                <option value="received" @selected($labReq['status'] === 'received')>
                                    Resultados recibidos
                                </option>
                            </select>
                        </div>
                        @if (! $finalized)
                            <button
                                wire:click="deleteLabRequest('{{ $labReq['id'] }}')"
                                data-swal-confirm="¿Eliminar esta solicitud y todos sus datos?"
                                class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition p-1 flex-shrink-0"
                                title="Eliminar"
                            >
                                <flux:icon.trash class="size-4" />
                            </button>
                        @endif
                    </div>

                    <div class="p-4 space-y-3 bg-white dark:bg-zinc-900">
                        {{-- Diagnóstico presuntivo --}}
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide"
                            >
                                Diagnóstico presuntivo
                            </label>
                            <input
                                type="text"
                                value="{{ $labReq['presumptive_diagnosis'] }}"
                                wire:change="saveField('{{ $labReq['id'] }}', 'presumptive_diagnosis', $event.target.value)"
                                @disabled($finalized)
                                placeholder="Ej: Posible anemia ferropénica, Diabetes en estudio..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 disabled:opacity-50"
                            />
                        </div>

                        {{-- Parámetros solicitados --}}
                        @if (count($labReq['items']) > 0)
                            <div class="rounded-lg border border-gray-200 dark:border-zinc-700 overflow-hidden">
                                <table class="w-full text-sm" dusk="lab-items-table">
                                    <thead>
                                        <tr
                                            class="bg-gray-50 dark:bg-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide"
                                        >
                                            <th class="px-3 py-2 text-left font-medium">Parámetro</th>
                                            <th class="px-3 py-2 text-left font-medium">Valor</th>
                                            <th class="px-3 py-2 text-left font-medium">Informe</th>
                                            <th class="px-3 py-2 text-center font-medium w-8">⚠</th>
                                            <th class="px-2 py-2 w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                        @foreach ($labReq['items'] as $item)
                                            @if ($labReq['status'] === 'pending' && ! $finalized)
                                                @if (count($item['results']) > 0)
                                                    {{-- Item already has a result: show read-only, no inputs --}}
                                                    @php
                                                        $savedResult = $item['results'][0];
                                                    @endphp

                                                    <tr class="bg-green-50/40 dark:bg-green-900/5" dusk="lab-item">
                                                        <td
                                                            class="px-3 py-2 font-medium text-gray-800 dark:text-gray-200"
                                                        >
                                                            {{ $item['parameter_name'] ?? '(examen completo)' }}
                                                        </td>
                                                        <td
                                                            class="px-3 py-2 font-medium {{ $savedResult['is_abnormal'] ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200' }}"
                                                        >
                                                            {{ $savedResult['value'] ?: '—' }}
                                                        </td>
                                                        <td
                                                            class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400 italic"
                                                        >
                                                            {{ $savedResult['report_text'] ?? '' }}
                                                        </td>
                                                        <td class="px-3 py-2 text-center">
                                                            @if ($savedResult['is_abnormal'])
                                                                <flux:icon.exclamation-triangle
                                                                    class="size-3.5 text-red-500 mx-auto"
                                                                />
                                                            @else
                                                                <flux:icon.check
                                                                    class="size-3.5 text-green-500 mx-auto"
                                                                />
                                                            @endif
                                                        </td>
                                                        <td class="px-2 py-2 text-center">
                                                            <button
                                                                wire:click="deleteResult('{{ $savedResult['id'] }}')"
                                                                class="text-gray-400 hover:text-red-500 transition"
                                                                title="Borrar resultado"
                                                            >
                                                                <flux:icon.x-mark class="size-3.5" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @else
                                                    {{-- No result yet: show input row --}}
                                                    <tr class="bg-white dark:bg-zinc-900" dusk="lab-item">
                                                        <td
                                                            class="px-3 py-2 font-medium text-gray-800 dark:text-gray-200 align-middle"
                                                        >
                                                            {{ $item['parameter_name'] ?? '(examen completo)' }}
                                                        </td>
                                                        <td class="px-3 py-2 align-middle">
                                                            <input
                                                                wire:model="newResults.{{ $item['id'] }}.value"
                                                                type="text"
                                                                placeholder="Valor"
                                                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-sky-500"
                                                            />
                                                        </td>
                                                        <td class="px-3 py-2 align-middle">
                                                            <input
                                                                wire:model="newResults.{{ $item['id'] }}.report"
                                                                type="text"
                                                                placeholder="Informe libre..."
                                                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-sky-500"
                                                            />
                                                        </td>
                                                        <td class="px-3 py-2 text-center align-middle">
                                                            <input
                                                                type="checkbox"
                                                                wire:model="newResults.{{ $item['id'] }}.abnormal"
                                                                class="rounded border-gray-300 text-red-500 focus:ring-red-400"
                                                                title="Resultado anormal"
                                                            />
                                                        </td>
                                                        <td class="px-2 py-2 text-center align-middle">
                                                            <button
                                                                wire:click="removeItem('{{ $item['id'] }}')"
                                                                wire:loading.attr="disabled"
                                                                class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition"
                                                                title="Eliminar parámetro"
                                                                dusk="lab-remove-item"
                                                            >
                                                                <flux:icon.trash class="size-4" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @else
                                                {{-- Received / finalized view --}}
                                                @php
                                                    $result = $item['results'][0] ?? null;
                                                @endphp

                                                <tr class="bg-white dark:bg-zinc-900" dusk="lab-item">
                                                    <td class="px-3 py-2 font-medium text-gray-800 dark:text-gray-200">
                                                        {{ $item['parameter_name'] ?? '(examen completo)' }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-2 font-medium {{ $result && $result['is_abnormal'] ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200' }}"
                                                    >
                                                        {{ $result ? ($result['value'] ?: '—') : '—' }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400 italic"
                                                    >
                                                        {{ $result['report_text'] ?? '' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        @if ($result && $result['is_abnormal'])
                                                            <flux:icon.exclamation-triangle
                                                                class="size-3.5 text-red-500 mx-auto"
                                                            />
                                                        @elseif ($result)
                                                            <flux:icon.check class="size-3.5 text-green-500 mx-auto" />
                                                        @endif
                                                    </td>

                                                    @if (! $finalized)
                                                        <td class="px-2 py-2 text-center">
                                                            <button
                                                                wire:click="removeItem('{{ $item['id'] }}')"
                                                                wire:loading.attr="disabled"
                                                                class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition"
                                                                title="Eliminar"
                                                                dusk="lab-remove-item"
                                                            >
                                                                <flux:icon.trash class="size-4" />
                                                            </button>
                                                        </td>
                                                    @else
                                                        <td></td>
                                                    @endif
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>

                                {{-- Single save button for all pending inputs --}}
                                @if ($labReq['status'] === 'pending' && ! $finalized)
                                    @php
                                        $hasPendingInputs = collect($labReq['items'])->contains(fn ($i) => count($i['results']) === 0);
                                    @endphp

                                    @if ($hasPendingInputs)
                                        <div
                                            class="px-4 py-3 border-t border-gray-100 dark:border-zinc-800 flex justify-end bg-gray-50 dark:bg-zinc-800/40"
                                        >
                                            <button
                                                wire:click="saveAllResults('{{ $labReq['id'] }}')"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium transition disabled:opacity-50"
                                            >
                                                <flux:icon.check class="size-4" />
                                                Guardar resultados
                                            </button>
                                        </div>
                                    @endif
                                @endif

                                {{-- File attachments per item --}}
                                @if ($labReq['status'] === 'received' || ! $finalized)
                                    @foreach ($labReq['items'] as $item)
                                        @if (count($item['attachments']) > 0 || ! $finalized)
                                            <div
                                                class="px-4 py-2 border-t border-gray-100 dark:border-zinc-800 flex flex-wrap gap-2 items-center bg-gray-50 dark:bg-zinc-800/40"
                                            >
                                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mr-1">
                                                    {{ $item['parameter_name'] ?? '(examen completo)' }}:
                                                </span>
                                                @php
                                                    $itemViewerItems = collect($item['attachments'])
                                                        ->map(
                                                            fn ($att) => [
                                                                'id' => $att['id'],
                                                                'name' => $att['original_name'] ?? 'Archivo',
                                                                'url' => $att['url'],
                                                                'type' => $att['is_pdf'] ? 'pdf' : 'image',
                                                            ],
                                                        )
                                                        ->values()
                                                        ->all();
                                                @endphp

                                                <x-lab-attachments-viewer
                                                    :items="$itemViewerItems"
                                                    :can-delete="! $finalized"
                                                />

                                                @if (! $finalized)
                                                    @if ($attachingToItemId === $item['id'] && $attachingToRequestId === $labReq['id'])
                                                        <label
                                                            class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded border border-sky-300 dark:border-sky-700 bg-sky-50 dark:bg-sky-900/20 text-xs text-sky-700 dark:text-sky-300 cursor-pointer hover:bg-sky-100 transition"
                                                        >
                                                            <flux:icon.paper-clip class="size-3" />
                                                            Elegir archivo
                                                            <input
                                                                type="file"
                                                                wire:model="newAttachmentFile"
                                                                accept=".jpg,.jpeg,.png,.webp,.pdf"
                                                                class="sr-only"
                                                            />
                                                        </label>
                                                        @if ($newAttachmentFile)
                                                            <button
                                                                wire:click="uploadAttachment"
                                                                wire:loading.attr="disabled"
                                                                class="px-2 py-0.5 text-xs rounded bg-sky-600 hover:bg-sky-700 text-white transition disabled:opacity-50"
                                                            >
                                                                Subir
                                                            </button>
                                                        @endif

                                                        <button
                                                            wire:click="$set('attachingToItemId', null)"
                                                            class="text-gray-400 hover:text-gray-600 transition text-xs"
                                                        >
                                                            ×
                                                        </button>
                                                    @else
                                                        <button
                                                            wire:click="openAttachment('{{ $labReq['id'] }}', '{{ $item['id'] }}')"
                                                            class="text-xs text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1"
                                                        >
                                                            <flux:icon.paper-clip class="size-3" />
                                                            Adjuntar
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-gray-400 dark:text-zinc-500 italic">Sin parámetros definidos.</p>
                        @endif

                        {{-- Marcar como recibido: cuando pending y hay al menos un resultado --}}
                        @php
                            $hasAnyResults = collect($labReq['items'])->contains(fn ($i) => count($i['results']) > 0);
                        @endphp

                        @if ($labReq['status'] === 'pending' && $hasAnyResults && ! $finalized)
                            <div class="mt-3 flex justify-end">
                                <button
                                    wire:click="saveField('{{ $labReq['id'] }}', 'status', 'received')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs font-semibold transition"
                                >
                                    <flux:icon.check class="size-3.5" />
                                    Marcar como recibido
                                </button>
                            </div>
                        @endif

                        {{-- Archivos de toda la solicitud (siempre que se pueda editar o ya esté recibida) --}}
                        @if ($labReq['status'] === 'received' || ! $finalized)
                            <div class="rounded-xl border border-gray-200 dark:border-zinc-700 p-3">
                                <p
                                    class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2"
                                >
                                    Archivos de la solicitud
                                </p>
                                @php
                                    $orderViewerItems = collect($labReq['orderAttachments'])
                                        ->map(
                                            fn ($att) => [
                                                'id' => $att['id'],
                                                'name' => $att['original_name'] ?? 'Archivo',
                                                'url' => $att['url'],
                                                'type' => $att['is_pdf'] ? 'pdf' : 'image',
                                            ],
                                        )
                                        ->values()
                                        ->all();
                                @endphp

                                <x-lab-attachments-viewer :items="$orderViewerItems" :can-delete="! $finalized" />

                                @if (! $finalized)
                                    <div class="mt-3">
                                        @if ($attachingToItemId === null && $attachingToRequestId === $labReq['id'])
                                            <label
                                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded border border-sky-300 dark:border-sky-700 bg-sky-50 dark:bg-sky-900/20 text-xs text-sky-700 dark:text-sky-300 cursor-pointer hover:bg-sky-100 transition"
                                            >
                                                <flux:icon.paper-clip class="size-3" />
                                                Elegir archivo
                                                <input
                                                    type="file"
                                                    wire:model="newAttachmentFile"
                                                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                                                    class="sr-only"
                                                />
                                            </label>
                                            @if ($newAttachmentFile)
                                                <button
                                                    wire:click="uploadAttachment"
                                                    wire:loading.attr="disabled"
                                                    class="px-2 py-1 text-xs rounded bg-sky-600 hover:bg-sky-700 text-white transition disabled:opacity-50"
                                                >
                                                    Subir
                                                </button>
                                            @endif

                                            <button
                                                wire:click="$set('attachingToRequestId', null)"
                                                class="text-gray-400 hover:text-gray-600 transition text-xs"
                                            >
                                                ×
                                            </button>
                                        @else
                                            <button
                                                wire:click="openAttachment('{{ $labReq['id'] }}')"
                                                class="text-xs text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1"
                                            >
                                                <flux:icon.paper-clip class="size-3" />
                                                Adjuntar a la solicitud
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Observaciones --}}
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide"
                            >
                                Observaciones
                            </label>
                            <textarea
                                wire:change="saveField('{{ $labReq['id'] }}', 'observations', $event.target.value)"
                                @disabled($finalized)
                                rows="2"
                                placeholder="Instrucciones para el paciente..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm resize-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 disabled:opacity-50"
                            >
                        {{ $labReq['observations'] }}</textarea
                            >
                        </div>
                    </div>
                </div>
            @empty
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
                    <p class="text-sm">Sin solicitudes de laboratorio.</p>
                    @if (! $finalized)
                        <p class="text-xs mt-1">Usa "Agregar Lab" para comenzar.</p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</section>
