<?php

use App\Contracts\PrescriptionItemServiceContract;
use App\Contracts\PrescriptionServiceContract;
use App\DTOs\PrescriptionDTO;
use App\DTOs\PrescriptionItemDTO;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionTemplate;
use App\ValueObjects\ConsultationStatus;
use Livewire\Component;

new class extends Component {
    public string $consultationId;
    public bool $finalized = false;
    public string $errorMessage = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $prescriptions = [];

    /** @var array<int, array{id: string, name: string}> */
    public array $prescriptionTemplates = [];

    public bool $showNewForm = false;
    public string $newReason = '';
    public string $newTemplateId = '';

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;

        $consultation = Consultation::findOrFail($this->consultationId);
        $this->prescriptionTemplates = PrescriptionTemplate::where('doctor_id', $consultation->doctor_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
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

        $this->prescriptions = Prescription::with('items')
            ->where('consultation_id', $this->consultationId)
            ->orderBy('created_at')
            ->get()
            ->map(
                fn ($p) => [
                    'id' => $p->id,
                    'reason' => $p->reason ?? '',
                    'items' => $p->items
                        ->map(
                            fn ($i) => [
                                'id' => $i->id,
                                'medication_name' => $i->medication_name,
                                'dose' => $i->dose,
                                'quantity' => $i->quantity ?? '',
                                'frequency' => $i->frequency,
                                'duration' => $i->duration,
                                'instructions' => $i->instructions ?? '',
                            ],
                        )
                        ->values()
                        ->all(),
                ],
            )
            ->values()
            ->all();
    }

    /**
     * Auto-save: se llama en wire:change de cada input del ítem.
     * Actualiza el campo en el array local y persiste el ítem completo en BD.
     */
    public function updateItemField(string $prescriptionId, string $itemId, string $field, string $value): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        $allowedFields = ['medication_name', 'dose', 'quantity', 'frequency', 'duration', 'instructions'];
        if (! in_array($field, $allowedFields, true)) {
            return;
        }

        $pIndex = array_search($prescriptionId, array_column($this->prescriptions, 'id'));
        if ($pIndex === false) {
            return;
        }
        $iIndex = array_search($itemId, array_column($this->prescriptions[$pIndex]['items'], 'id'));
        if ($iIndex === false) {
            return;
        }

        $this->prescriptions[$pIndex]['items'][$iIndex][$field] = $value;
        $item = $this->prescriptions[$pIndex]['items'][$iIndex];

        try {
            $dto = new PrescriptionItemDTO(
                medication_name: $item['medication_name'],
                dose: $item['dose'],
                quantity: $item['quantity'] !== '' ? $item['quantity'] : null,
                frequency: $item['frequency'],
                duration: $item['duration'],
                instructions: $item['instructions'] !== '' ? $item['instructions'] : null,
            );
            app(PrescriptionItemServiceContract::class)->update($itemId, $dto);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar: ' . $e->getMessage();
        }
    }

    public function createPrescription(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            $dto = new PrescriptionDTO(
                reason: trim($this->newReason) !== '' ? trim($this->newReason) : null,
                observations: null,
            );
            $prescription = app(PrescriptionServiceContract::class)->createForConsultation($this->consultationId, $dto);

            $this->newReason = '';
            $this->showNewForm = false;

            if ($this->newTemplateId !== '') {
                app(PrescriptionServiceContract::class)->applyTemplate($prescription->id, $this->newTemplateId);
                $this->newTemplateId = '';
                $this->reload();
            } else {
                $this->prescriptions[] = [
                    'id' => $prescription->id,
                    'reason' => $prescription->reason ?? '',
                    'items' => [],
                ];
                $this->addEmptyItem($prescription->id);
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al crear receta: ' . $e->getMessage();
        }
    }

    public function applyTemplate(string $prescriptionId, string $templateId): void
    {
        if ($this->finalized || $templateId === '') {
            return;
        }

        $this->errorMessage = '';

        try {
            app(PrescriptionServiceContract::class)->applyTemplate($prescriptionId, $templateId);
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al aplicar plantilla: ' . $e->getMessage();
        }
    }

    public function deletePrescription(string $prescriptionId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            app(PrescriptionServiceContract::class)->delete($prescriptionId);
            $this->prescriptions = array_values(
                array_filter($this->prescriptions, fn ($p) => $p['id'] !== $prescriptionId),
            );
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar receta: ' . $e->getMessage();
        }
    }

    public function removeItem(string $prescriptionId, string $itemId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            app(PrescriptionItemServiceContract::class)->delete($itemId);
            $pIndex = array_search($prescriptionId, array_column($this->prescriptions, 'id'));
            if ($pIndex !== false) {
                $this->prescriptions[$pIndex]['items'] = array_values(
                    array_filter($this->prescriptions[$pIndex]['items'], fn ($i) => $i['id'] !== $itemId),
                );
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar medicamento: ' . $e->getMessage();
        }
    }

    public function createFromTemplate(string $templateId): void
    {
        if ($this->finalized || $templateId === '') {
            return;
        }

        $this->errorMessage = '';

        try {
            $dto = new PrescriptionDTO(reason: null, observations: null);
            $prescription = app(PrescriptionServiceContract::class)->createForConsultation($this->consultationId, $dto);
            app(PrescriptionServiceContract::class)->applyTemplate($prescription->id, $templateId);
            $this->reload();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al crear receta con plantilla: ' . $e->getMessage();
        }
    }

    public function addEmptyItem(string $prescriptionId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            $dto = new PrescriptionItemDTO(
                medication_name: '',
                dose: '',
                quantity: null,
                frequency: '',
                duration: '',
                instructions: null,
            );

            $item = app(PrescriptionItemServiceContract::class)->create($prescriptionId, $dto);

            $pIndex = array_search($prescriptionId, array_column($this->prescriptions, 'id'));
            if ($pIndex !== false) {
                $this->prescriptions[$pIndex]['items'][] = [
                    'id' => $item->id,
                    'medication_name' => '',
                    'dose' => '',
                    'quantity' => '',
                    'frequency' => '',
                    'duration' => '',
                    'instructions' => '',
                ];
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al agregar fila: ' . $e->getMessage();
        }
    }
}; ?>

@php
    $inp = 'w-full px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition';
    $inpSm = 'w-full px-2 py-1.5 rounded-md border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-zinc-500 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition';
    // Textareas transparentes estilo Excel (auto-grow vía Alpine x-init)
    $inpCell = 'w-full px-2 py-1.5 bg-transparent border-none outline-none text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:bg-emerald-50 dark:focus:bg-emerald-900/10 resize-none overflow-hidden transition-colors';
    $inpCellArea = 'w-full px-2 py-1 bg-transparent border-none outline-none text-xs text-gray-500 dark:text-gray-400 placeholder-gray-400 dark:placeholder-zinc-500 focus:bg-emerald-50 dark:focus:bg-emerald-900/10 resize-none overflow-hidden transition-colors';
@endphp

<section id="receta" dusk="section-prescription" class="scroll-mt-16">
    <div
        class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                    <svg
                        class="w-4 h-4 text-emerald-600 dark:text-emerald-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                        />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Receta Médica</h2>
                    @if (count($prescriptions) > 0)
                        <p class="text-xs text-gray-400 dark:text-zinc-500">{{ count($prescriptions) }} receta(s)</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span wire:loading wire:target="updateItemField" class="text-xs text-emerald-500 dark:text-emerald-400">
                    Guardando…
                </span>
                @if (! $finalized)
                    <button
                        wire:click="$set('showNewForm', true)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition"
                    >
                        <flux:icon.plus class="size-4" />
                        Nueva Receta
                    </button>
                @else
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400"
                    >
                        Finalizada
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-4">
            @if ($errorMessage)
                <div
                    class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300"
                >
                    {{ $errorMessage }}
                </div>
            @endif

            {{-- Form nueva receta --}}
            @if ($showNewForm && ! $finalized)
                <div
                    class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/10 p-4 space-y-3"
                >
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">
                        Nueva Receta
                    </p>
                    <input
                        wire:model="newReason"
                        type="text"
                        placeholder="Diagnóstico (ej: Fiebre por dengue, Resfriado común)"
                        class="{{ $inp }}"
                    />
                    @if (count($prescriptionTemplates) > 0)
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap">Plantilla:</label>
                            <select wire:model="newTemplateId" class="{{ $inp }}">
                                <option value="">— Sin plantilla (agregar manualmente) —</option>
                                @foreach ($prescriptionTemplates as $tpl)
                                    <option value="{{ $tpl['id'] }}">{{ $tpl['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <button
                            wire:click="createPrescription"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition disabled:opacity-50"
                        >
                            Crear
                        </button>
                        <button
                            wire:click="$set('showNewForm', false)"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-800 text-sm transition"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            @endif

            {{-- Lista de recetas --}}
            @forelse ($prescriptions as $pIndex => $prescription)
                <div
                    wire:key="prescription-{{ $prescription['id'] }}"
                    class="rounded-xl border border-gray-200 dark:border-zinc-700 overflow-hidden"
                >
                    {{-- Encabezado de receta --}}
                    <div
                        class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold"
                            >
                                {{ $pIndex + 1 }}
                            </span>

                            @if ($prescription['reason'] !== '')
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $prescription['reason'] }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400 dark:text-zinc-500 italic">
                                    Sin diagnóstico especificado
                                </span>
                            @endif
                        </div>
                        @if (! $finalized)
                            <button
                                wire:click="deletePrescription('{{ $prescription['id'] }}')"
                                wire:confirm="¿Eliminar esta receta y todos sus medicamentos?"
                                class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition p-1"
                                title="Eliminar receta"
                            >
                                <flux:icon.trash class="size-4" />
                            </button>
                        @endif
                    </div>

                    {{-- Tabla de medicamentos --}}
                    <div class="overflow-x-auto bg-white dark:bg-zinc-900">
                        @if (count($prescription['items']) > 0)
                            <table class="w-full text-sm border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-zinc-800">
                                        <th
                                            class="border border-gray-200 dark:border-zinc-700 px-2 py-1.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide"
                                        >
                                            Medicamento
                                        </th>
                                        <th
                                            class="border border-gray-200 dark:border-zinc-700 px-2 py-1.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide"
                                        >
                                            Dosis
                                        </th>
                                        <th
                                            class="border border-gray-200 dark:border-zinc-700 px-2 py-1.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide"
                                        >
                                            Cantidad
                                        </th>
                                        <th
                                            class="border border-gray-200 dark:border-zinc-700 px-2 py-1.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide"
                                        >
                                            Frecuencia
                                        </th>
                                        <th
                                            class="border border-gray-200 dark:border-zinc-700 px-2 py-1.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide"
                                        >
                                            Duración
                                        </th>
                                        @if (! $finalized)
                                            <th class="border border-gray-200 dark:border-zinc-700 w-9"></th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($prescription['items'] as $iIndex => $item)
                                        {{-- Fila 1 — campos principales --}}
                                        <tr
                                            wire:key="rx-item-{{ $item['id'] }}-main"
                                            class="bg-white dark:bg-zinc-900 hover:bg-gray-50/40 dark:hover:bg-zinc-800/30"
                                            dusk="rx-item"
                                        >
                                            <td class="border border-gray-200 dark:border-zinc-700 p-0">
                                                @if (! $finalized)
                                                    <textarea
                                                        x-data
                                                        x-init="$el.value = $el.dataset.val || '';
                                                    $el.style.height = $el.scrollHeight + 'px'"
                                                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                                        data-val="{{ $item['medication_name'] }}"
                                                        wire:change="updateItemField('{{ $prescription['id'] }}', '{{ $item['id'] }}', 'medication_name', $event.target.value)"
                                                        rows="1"
                                                        placeholder="Medicamento *"
                                                        class="{{ $inpCell }}"
                                                    ></textarea>
                                                @else
                                                    <span
                                                        class="block px-2 py-1.5 font-medium text-gray-900 dark:text-gray-100"
                                                    >
                                                        {{ $item['medication_name'] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="border border-gray-200 dark:border-zinc-700 p-0">
                                                @if (! $finalized)
                                                    <textarea
                                                        x-data
                                                        x-init="$el.value = $el.dataset.val || '';
                                                    $el.style.height = $el.scrollHeight + 'px'"
                                                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                                        data-val="{{ $item['dose'] }}"
                                                        wire:change="updateItemField('{{ $prescription['id'] }}', '{{ $item['id'] }}', 'dose', $event.target.value)"
                                                        rows="1"
                                                        placeholder="Dosis *"
                                                        class="{{ $inpCell }}"
                                                    ></textarea>
                                                @else
                                                    <span class="block px-2 py-1.5 text-gray-700 dark:text-gray-300">
                                                        {{ $item['dose'] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="border border-gray-200 dark:border-zinc-700 p-0">
                                                @if (! $finalized)
                                                    <textarea
                                                        x-data
                                                        x-init="$el.value = $el.dataset.val || '';
                                                    $el.style.height = $el.scrollHeight + 'px'"
                                                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                                        data-val="{{ $item['quantity'] }}"
                                                        wire:change="updateItemField('{{ $prescription['id'] }}', '{{ $item['id'] }}', 'quantity', $event.target.value)"
                                                        rows="1"
                                                        placeholder="Cantidad"
                                                        class="{{ $inpCell }}"
                                                    ></textarea>
                                                @else
                                                    <span class="block px-2 py-1.5 text-gray-700 dark:text-gray-300">
                                                        {{ $item['quantity'] ?: '—' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="border border-gray-200 dark:border-zinc-700 p-0">
                                                @if (! $finalized)
                                                    <textarea
                                                        x-data
                                                        x-init="$el.value = $el.dataset.val || '';
                                                    $el.style.height = $el.scrollHeight + 'px'"
                                                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                                        data-val="{{ $item['frequency'] }}"
                                                        wire:change="updateItemField('{{ $prescription['id'] }}', '{{ $item['id'] }}', 'frequency', $event.target.value)"
                                                        rows="1"
                                                        placeholder="Frecuencia *"
                                                        class="{{ $inpCell }}"
                                                    ></textarea>
                                                @else
                                                    <span class="block px-2 py-1.5 text-gray-700 dark:text-gray-300">
                                                        {{ $item['frequency'] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="border border-gray-200 dark:border-zinc-700 p-0">
                                                @if (! $finalized)
                                                    <textarea
                                                        x-data
                                                        x-init="$el.value = $el.dataset.val || '';
                                                    $el.style.height = $el.scrollHeight + 'px'"
                                                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                                        data-val="{{ $item['duration'] }}"
                                                        wire:change="updateItemField('{{ $prescription['id'] }}', '{{ $item['id'] }}', 'duration', $event.target.value)"
                                                        rows="1"
                                                        placeholder="Duración *"
                                                        class="{{ $inpCell }}"
                                                    ></textarea>
                                                @else
                                                    <span class="block px-2 py-1.5 text-gray-700 dark:text-gray-300">
                                                        {{ $item['duration'] }}
                                                    </span>
                                                @endif
                                            </td>
                                            @if (! $finalized)
                                                <td
                                                    rowspan="2"
                                                    class="border border-gray-200 dark:border-zinc-700 text-center align-middle w-9"
                                                >
                                                    <button
                                                        wire:click="removeItem('{{ $prescription['id'] }}', '{{ $item['id'] }}')"
                                                        wire:confirm="¿Eliminar este medicamento?"
                                                        wire:loading.attr="disabled"
                                                        dusk="rx-remove-item"
                                                        class="p-1 rounded text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-300 transition"
                                                        title="Eliminar"
                                                    >
                                                        <flux:icon.trash class="size-3.5" />
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                        {{-- Fila 2 — instrucciones (compacta) --}}
                                        <tr
                                            wire:key="rx-item-{{ $item['id'] }}-instr"
                                            class="bg-gray-50/60 dark:bg-zinc-800/40"
                                        >
                                            <td colspan="5" class="border border-gray-200 dark:border-zinc-700 p-0">
                                                @if (! $finalized)
                                                    <textarea
                                                        x-data
                                                        x-init="$el.value = $el.dataset.val || '';
                                                    $el.style.height = $el.scrollHeight + 'px'"
                                                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                                        data-val="{{ $item['instructions'] }}"
                                                        wire:change="updateItemField('{{ $prescription['id'] }}', '{{ $item['id'] }}', 'instructions', $event.target.value)"
                                                        rows="1"
                                                        placeholder="Instrucciones (opcional)"
                                                        class="{{ $inpCellArea }}"
                                                    ></textarea>
                                                @elseif ($item['instructions'])
                                                    <p
                                                        class="px-2 py-1 text-xs text-gray-400 dark:text-zinc-500 italic"
                                                    >
                                                        {{ $item['instructions'] }}
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="px-4 py-4 text-sm text-gray-400 dark:text-zinc-500 italic">
                                Sin medicamentos aún.
                            </p>
                        @endif
                    </div>

                    @if (! $finalized)
                        <div class="border-t border-gray-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2">
                            <button
                                wire:click="addEmptyItem('{{ $prescription['id'] }}')"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition font-medium"
                            >
                                <flux:icon.plus class="size-3.5" />
                                Añadir fila
                            </button>
                        </div>
                    @endif
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
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                    <p class="text-sm">Sin recetas aún.</p>
                    @if (! $finalized)
                        <p class="text-xs mt-1">Usa el botón "Nueva Receta" para comenzar.</p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</section>
