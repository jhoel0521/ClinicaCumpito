<?php

use App\Contracts\LaboratoryRequestItemServiceContract;
use App\Contracts\LaboratoryRequestServiceContract;
use App\DTOs\LaboratoryRequestDTO;
use App\DTOs\LaboratoryRequestItemDTO;
use App\Models\Consultation;
use App\Models\LaboratoryAttachment;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryItemResult;
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
                            'parameter_name' => $res->parameter_name,
                            'value' => $res->value,
                            'reference_range' => $res->reference_range,
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
        }
    }

    public function saveField(string $requestId, string $field, string $value): void
    {
        if ($this->finalized) {
            return;
        }

        $allowed = ['status', 'presumptive_diagnosis', 'observations'];
        if (! in_array($field, $allowed, true)) {
            return;
        }

        $rIndex = array_search($requestId, array_column($this->labRequests, 'id'));
        if ($rIndex === false) {
            return;
        }

        $this->labRequests[$rIndex][$field] = $value;
        $r = $this->labRequests[$rIndex];

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
        }
    }

    // ── Results ──

    public function saveNewResult(string $itemId): void
    {
        $this->errorMessage = '';

        try {
            $data = $this->newResults[$itemId] ?? [];

            LaboratoryItemResult::create([
                'laboratory_request_item_id' => $itemId,
                'parameter_name' => ! empty($data['paramName']) ? trim($data['paramName']) : null,
                'value' => ! empty($data['value']) ? trim($data['value']) : null,
                'reference_range' => ! empty($data['range']) ? trim($data['range']) : null,
                'report_text' => ! empty($data['report']) ? trim($data['report']) : null,
                'is_abnormal' => ! empty($data['abnormal']),
            ]);

            $this->newResults[$itemId] = [];
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar resultado: ' . $e->getMessage();
        }
    }

    public function deleteResult(string $resultId): void
    {
        $this->errorMessage = '';

        try {
            LaboratoryItemResult::findOrFail($resultId)->delete();
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar resultado: ' . $e->getMessage();
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

            {{-- ── Lista de laboratorios ── --}}
            @forelse ($labRequests as $labReq)
                <div class="rounded-xl border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    {{-- Encabezado: nombre del examen + estado + eliminar --}}
                    <div
                        class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">
                                {{ $labReq['examName'] }}
                            </span>
                            <select
                                wire:change="saveField('{{ $labReq['id'] }}', 'status', $event.target.value)"
                                @disabled($finalized)
                                class="text-xs rounded-full px-2 py-0.5 border font-medium transition focus:ring-1 focus:ring-sky-500 disabled:opacity-50 flex-shrink-0 @if ($labReq['status'] === 'pending') bg-amber-50 dark:bg-amber-900/20 border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 @else bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700 text-green-700 dark:text-green-300 @endif"
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
                                wire:confirm="¿Eliminar esta solicitud y todos sus datos?"
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
                                <div class="divide-y divide-gray-100 dark:divide-zinc-800">
                                    @foreach ($labReq['items'] as $item)
                                        <div class="px-4 py-3 bg-white dark:bg-zinc-900" dusk="lab-item">
                                            <div class="flex items-start justify-between gap-3">
                                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                                    {{ $item['parameter_name'] ?? '(examen completo)' }}
                                                </span>
                                                @if (! $finalized)
                                                    <button
                                                        wire:click="removeItem('{{ $item['id'] }}')"
                                                        wire:loading.attr="disabled"
                                                        class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition p-0.5 flex-shrink-0"
                                                        title="Eliminar"
                                                        dusk="lab-remove-item"
                                                    >
                                                        <flux:icon.trash class="size-4" />
                                                    </button>
                                                @endif
                                            </div>

                                            {{-- Resultados — solo si status = received ── --}}
                                            @if ($labReq['status'] === 'received')
                                                <div class="mt-3 space-y-2">
                                                    {{-- Tabla de resultados existentes --}}
                                                    @if (count($item['results']) > 0)
                                                        <div
                                                            class="rounded-lg border border-gray-200 dark:border-zinc-700 overflow-hidden"
                                                        >
                                                            <table class="w-full text-xs">
                                                                <thead>
                                                                    <tr
                                                                        class="bg-gray-50 dark:bg-zinc-800 text-gray-500 dark:text-gray-400 uppercase tracking-wide"
                                                                    >
                                                                        <th class="px-3 py-1.5 text-left font-medium">
                                                                            Parámetro
                                                                        </th>
                                                                        <th class="px-3 py-1.5 text-left font-medium">
                                                                            Valor
                                                                        </th>
                                                                        <th class="px-3 py-1.5 text-left font-medium">
                                                                            Referencia
                                                                        </th>
                                                                        <th
                                                                            class="px-3 py-1.5 text-center font-medium w-8"
                                                                        >
                                                                            ⚠
                                                                        </th>
                                                                        @if (! $finalized)
                                                                            <th class="w-6"></th>
                                                                        @endif
                                                                    </tr>
                                                                </thead>
                                                                <tbody
                                                                    class="divide-y divide-gray-100 dark:divide-zinc-800"
                                                                >
                                                                    @foreach ($item['results'] as $result)
                                                                        <tr class="bg-white dark:bg-zinc-900">
                                                                            <td
                                                                                class="px-3 py-2 text-gray-700 dark:text-gray-300"
                                                                            >
                                                                                {{ $result['parameter_name'] ?: '—' }}
                                                                            </td>
                                                                            <td
                                                                                class="px-3 py-2 font-medium {{ $result['is_abnormal'] ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200' }}"
                                                                            >
                                                                                {{ $result['value'] ?: '—' }}
                                                                            </td>
                                                                            <td
                                                                                class="px-3 py-2 text-gray-500 dark:text-gray-400"
                                                                            >
                                                                                {{ $result['reference_range'] ?: '—' }}
                                                                            </td>
                                                                            <td class="px-3 py-2 text-center">
                                                                                @if ($result['is_abnormal'])
                                                                                    <flux:icon.exclamation-triangle
                                                                                        class="size-3.5 text-red-500 mx-auto"
                                                                                    />
                                                                                @else
                                                                                    <flux:icon.check
                                                                                        class="size-3.5 text-green-500 mx-auto"
                                                                                    />
                                                                                @endif
                                                                            </td>
                                                                            @if (! $finalized)
                                                                                <td class="px-2 py-2">
                                                                                    <button
                                                                                        wire:click="deleteResult('{{ $result['id'] }}')"
                                                                                        class="text-red-400 hover:text-red-600 transition"
                                                                                    >
                                                                                        <flux:icon.x-mark
                                                                                            class="size-3.5"
                                                                                        />
                                                                                    </button>
                                                                                </td>
                                                                            @endif
                                                                        </tr>
                                                                        @if ($result['report_text'])
                                                                            <tr class="bg-gray-50 dark:bg-zinc-800/50">
                                                                                <td
                                                                                    colspan="{{ $finalized ? 4 : 5 }}"
                                                                                    class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400 italic"
                                                                                >
                                                                                    {{ $result['report_text'] }}
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    {{-- Formulario nueva respuesta --}}
                                                    @if (! $finalized)
                                                        <div
                                                            class="rounded-lg border border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-900/10 p-3 space-y-2"
                                                        >
                                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                                <input
                                                                    wire:model="newResults.{{ $item['id'] }}.paramName"
                                                                    type="text"
                                                                    placeholder="Parámetro (opcional)"
                                                                    class="px-2.5 py-1.5 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-sky-500"
                                                                />
                                                                <input
                                                                    wire:model="newResults.{{ $item['id'] }}.value"
                                                                    type="text"
                                                                    placeholder="Valor (opcional)"
                                                                    class="px-2.5 py-1.5 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-sky-500"
                                                                />
                                                                <input
                                                                    wire:model="newResults.{{ $item['id'] }}.range"
                                                                    type="text"
                                                                    placeholder="Referencia (opcional)"
                                                                    class="px-2.5 py-1.5 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-sky-500"
                                                                />
                                                            </div>
                                                            <textarea
                                                                wire:model="newResults.{{ $item['id'] }}.report"
                                                                rows="2"
                                                                placeholder="Informe / texto libre (radiología, cultivos...)"
                                                                class="w-full px-2.5 py-1.5 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm resize-none focus:ring-1 focus:ring-sky-500"
                                                            ></textarea>
                                                            <div class="flex items-center justify-between">
                                                                <label
                                                                    class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400 cursor-pointer"
                                                                >
                                                                    <input
                                                                        type="checkbox"
                                                                        wire:model="newResults.{{ $item['id'] }}.abnormal"
                                                                        class="rounded border-gray-300 text-red-500 focus:ring-red-400"
                                                                    />
                                                                    Resultado anormal
                                                                </label>
                                                                <div class="flex gap-2">
                                                                    <button
                                                                        wire:click="saveNewResult('{{ $item['id'] }}')"
                                                                        class="px-3 py-1 text-xs rounded bg-sky-600 hover:bg-sky-700 text-white transition"
                                                                    >
                                                                        Guardar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{-- Adjuntos del ítem --}}
                                                    <div>
                                                        <p
                                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"
                                                        >
                                                            Archivos del examen
                                                        </p>
                                                        <div class="flex flex-wrap gap-2 items-center">
                                                            @foreach ($item['attachments'] as $att)
                                                                <div
                                                                    class="inline-flex items-center gap-1 px-2 py-1 rounded border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-xs text-gray-700 dark:text-gray-300"
                                                                >
                                                                    @if ($att['is_image'])
                                                                        <a
                                                                            href="{{ $att['url'] }}"
                                                                            target="_blank"
                                                                            class="flex items-center gap-1 hover:text-sky-600 transition"
                                                                        >
                                                                            <flux:icon.photo
                                                                                class="size-3.5 text-sky-500"
                                                                            />
                                                                            {{ $att['original_name'] ?? 'Imagen' }}
                                                                        </a>
                                                                    @else
                                                                        <a
                                                                            href="{{ $att['url'] }}"
                                                                            target="_blank"
                                                                            class="flex items-center gap-1 hover:text-sky-600 transition"
                                                                        >
                                                                            <flux:icon.document
                                                                                class="size-3.5 text-red-500"
                                                                            />
                                                                            {{ $att['original_name'] ?? 'Documento' }}
                                                                        </a>
                                                                    @endif
                                                                    @if (! $finalized)
                                                                        <button
                                                                            wire:click="deleteAttachment('{{ $att['id'] }}')"
                                                                            class="ml-1 text-gray-400 hover:text-red-500 transition"
                                                                        >
                                                                            <flux:icon.x-mark class="size-3" />
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @endforeach

                                                            @if (! $finalized)
                                                                @if ($attachingToItemId === $item['id'] && $attachingToRequestId === $labReq['id'])
                                                                    <div class="flex items-center gap-2">
                                                                        <input
                                                                            type="file"
                                                                            wire:model="newAttachmentFile"
                                                                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                                                                            class="text-xs"
                                                                        />
                                                                        <button
                                                                            wire:click="uploadAttachment"
                                                                            wire:loading.attr="disabled"
                                                                            class="px-2 py-1 text-xs rounded bg-sky-600 hover:bg-sky-700 text-white transition disabled:opacity-50"
                                                                        >
                                                                            Subir
                                                                        </button>
                                                                        <button
                                                                            wire:click="$set('attachingToItemId', null)"
                                                                            class="px-2 py-1 text-xs rounded border border-gray-300 dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition"
                                                                        >
                                                                            ×
                                                                        </button>
                                                                    </div>
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
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 dark:text-zinc-500 italic">Sin parámetros definidos.</p>
                        @endif

                        {{-- Archivos de toda la solicitud (solo si received) --}}
                        @if ($labReq['status'] === 'received')
                            <div class="rounded-xl border border-gray-200 dark:border-zinc-700 p-3">
                                <p
                                    class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2"
                                >
                                    Archivos de la solicitud
                                </p>
                                <div class="flex flex-wrap gap-2 items-center">
                                    @foreach ($labReq['orderAttachments'] as $att)
                                        <div
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-xs text-gray-700 dark:text-gray-300"
                                        >
                                            @if ($att['is_image'])
                                                <a
                                                    href="{{ $att['url'] }}"
                                                    target="_blank"
                                                    class="flex items-center gap-1 hover:text-sky-600 transition"
                                                >
                                                    <flux:icon.photo class="size-3.5 text-sky-500" />
                                                    {{ $att['original_name'] ?? 'Imagen' }}
                                                </a>
                                            @else
                                                <a
                                                    href="{{ $att['url'] }}"
                                                    target="_blank"
                                                    class="flex items-center gap-1 hover:text-sky-600 transition"
                                                >
                                                    <flux:icon.document class="size-3.5 text-red-500" />
                                                    {{ $att['original_name'] ?? 'Documento' }}
                                                </a>
                                            @endif
                                            @if (! $finalized)
                                                <button
                                                    wire:click="deleteAttachment('{{ $att['id'] }}')"
                                                    class="ml-1 text-gray-400 hover:text-red-500 transition"
                                                >
                                                    <flux:icon.x-mark class="size-3" />
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if (! $finalized)
                                        @if ($attachingToItemId === null && $attachingToRequestId === $labReq['id'])
                                            <div class="flex items-center gap-2">
                                                <input
                                                    type="file"
                                                    wire:model="newAttachmentFile"
                                                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                                                    class="text-xs"
                                                />
                                                <button
                                                    wire:click="uploadAttachment"
                                                    wire:loading.attr="disabled"
                                                    class="px-2 py-1 text-xs rounded bg-sky-600 hover:bg-sky-700 text-white transition disabled:opacity-50"
                                                >
                                                    Subir
                                                </button>
                                                <button
                                                    wire:click="$set('attachingToRequestId', null)"
                                                    class="px-2 py-1 text-xs rounded border border-gray-300 dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition"
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        @else
                                            <button
                                                wire:click="openAttachment('{{ $labReq['id'] }}')"
                                                class="text-xs text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1"
                                            >
                                                <flux:icon.paper-clip class="size-3" />
                                                Adjuntar a la solicitud
                                            </button>
                                        @endif
                                    @endif
                                </div>
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
