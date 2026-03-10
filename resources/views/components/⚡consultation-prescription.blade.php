<?php

use App\Contracts\PrescriptionItemServiceContract;
use App\Contracts\PrescriptionServiceContract;
use App\DTOs\PrescriptionDTO;
use App\DTOs\PrescriptionItemDTO;
use App\Models\Consultation;
use App\Models\PrescriptionTemplate;
use App\ValueObjects\ConsultationStatus;
use Livewire\Component;

new class extends Component {
    public string $consultationId;

    /** @var array<int, array{id: string, name: string, description: string|null}> */
    public array $templates = [];

    public ?string $prescriptionId = null;
    public ?string $observations = null;

    /** @var array<int, array{id: string, medication_name: string, dose: string, frequency: string, duration: string, instructions: string|null, editing: bool}> */
    public array $items = [];

    /** @var array<int, array{template_name: string, applied_at: string}> */
    public array $appliedTemplates = [];

    public string $newMedicationName = '';
    public string $newDose = '';
    public string $newFrequency = '';
    public string $newDuration = '';
    public string $newInstructions = '';

    public bool $finalized = false;
    public bool $saved = false;
    public string $errorMessage = '';

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;

        $consultation = Consultation::with('prescription.items', 'prescription.appliedTemplates')->findOrFail(
            $consultationId,
        );

        $this->templates = PrescriptionTemplate::query()
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

        $prescription = $consultation->prescription;
        if ($prescription) {
            $this->prescriptionId = $prescription->id;
            $this->observations = $prescription->observations;
            $this->saved = true;
            $this->loadItems($prescription->items);
            $this->loadAppliedTemplates($prescription->appliedTemplates);
        }
    }

    /** @param \Illuminate\Support\Collection<int, \App\Models\PrescriptionItem> $items */
    private function loadItems(\Illuminate\Support\Collection $items): void
    {
        $this->items = $items
            ->map(
                fn ($item) => [
                    'id' => $item->id,
                    'medication_name' => $item->medication_name,
                    'dose' => $item->dose,
                    'frequency' => $item->frequency,
                    'duration' => $item->duration,
                    'instructions' => $item->instructions,
                    'editing' => false,
                ],
            )
            ->values()
            ->all();
    }

    /** @param \Illuminate\Support\Collection<int, \App\Models\PrescriptionAppliedTemplate> $applied */
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

    public function savePrescription(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        $this->validate([
            'observations' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $dto = new PrescriptionDTO(observations: $this->observations ?: null);
            $prescription = app(PrescriptionServiceContract::class)->upsert($this->consultationId, $dto);
            $this->prescriptionId = $prescription->id;
            $this->saved = true;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar la receta: ' . $e->getMessage();
        }
    }

    public function applyTemplate(string $templateId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        if (! $this->prescriptionId) {
            $this->savePrescription();
            if (! $this->prescriptionId) {
                return;
            }
        }

        try {
            $prescription = app(PrescriptionServiceContract::class)->applyTemplate($this->prescriptionId, $templateId);
            $this->loadItems($prescription->items);
            $this->loadAppliedTemplates($prescription->appliedTemplates);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al aplicar plantilla: ' . $e->getMessage();
        }
    }

    public function startEditing(string $itemId): void
    {
        $this->items = array_map(fn ($item) => array_merge($item, ['editing' => $item['id'] === $itemId]), $this->items);
    }

    public function saveItem(string $itemId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        $index = array_search($itemId, array_column($this->items, 'id'));
        if ($index === false) {
            return;
        }

        $item = $this->items[$index];

        try {
            $dto = new PrescriptionItemDTO(
                medication_name: $item['medication_name'],
                dose: $item['dose'],
                frequency: $item['frequency'],
                duration: $item['duration'],
                instructions: $item['instructions'] ?: null,
            );
            app(PrescriptionItemServiceContract::class)->update($itemId, $dto);
            $this->items[$index]['editing'] = false;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar cambios: ' . $e->getMessage();
        }
    }

    public function cancelEditing(string $itemId): void
    {
        $this->items = array_map(fn ($item) => array_merge($item, ['editing' => false]), $this->items);
    }

    public function addItem(): void
    {
        if ($this->finalized || ! $this->prescriptionId) {
            return;
        }

        $this->errorMessage = '';

        $this->validate(
            [
                'newMedicationName' => ['required', 'string', 'max:200'],
                'newDose' => ['required', 'string', 'max:100'],
                'newFrequency' => ['required', 'string', 'max:100'],
                'newDuration' => ['required', 'string', 'max:100'],
                'newInstructions' => ['nullable', 'string', 'max:500'],
            ],
            [
                'newMedicationName.required' => 'El medicamento es obligatorio.',
                'newDose.required' => 'La dosis es obligatoria.',
                'newFrequency.required' => 'La frecuencia es obligatoria.',
                'newDuration.required' => 'La duración es obligatoria.',
            ],
        );

        try {
            $dto = new PrescriptionItemDTO(
                medication_name: $this->newMedicationName,
                dose: $this->newDose,
                frequency: $this->newFrequency,
                duration: $this->newDuration,
                instructions: $this->newInstructions ?: null,
            );

            $item = app(PrescriptionItemServiceContract::class)->create($this->prescriptionId, $dto);

            $this->items[] = [
                'id' => $item->id,
                'medication_name' => $item->medication_name,
                'dose' => $item->dose,
                'frequency' => $item->frequency,
                'duration' => $item->duration,
                'instructions' => $item->instructions,
                'editing' => false,
            ];

            $this->newMedicationName = '';
            $this->newDose = '';
            $this->newFrequency = '';
            $this->newDuration = '';
            $this->newInstructions = '';
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al agregar ítem: ' . $e->getMessage();
        }
    }

    public function removeItem(string $itemId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            app(PrescriptionItemServiceContract::class)->delete($itemId);
            $this->items = array_values(array_filter($this->items, fn ($i) => $i['id'] !== $itemId));
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar: ' . $e->getMessage();
        }
    }
}; ?>

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
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Receta Médica</h2>
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
                <span
                    wire:loading
                    wire:target="savePrescription,applyTemplate,saveItem"
                    class="text-xs text-emerald-400"
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

            {{-- Observaciones generales --}}
            <div>
                <label
                    class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide"
                >
                    Observaciones generales
                </label>
                <textarea
                    wire:model="observations"
                    wire:change="savePrescription"
                    rows="2"
                    dusk="rx-observations"
                    @disabled($finalized)
                    placeholder="Instrucciones generales para el paciente..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm resize-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
                ></textarea>
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
                                    class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded px-1.5 py-0.5 text-xs mr-1"
                                >
                                    {{ $applied['template_name'] }}
                                    <span class="opacity-60">{{ $applied['applied_at'] }}</span>
                                </span>
                            @endforeach
                        </p>
                    @endif

                    <div class="flex flex-wrap gap-2" dusk="rx-templates-panel">
                        @foreach ($templates as $t)
                            <button
                                wire:click="applyTemplate('{{ $t['id'] }}')"
                                wire:loading.attr="disabled"
                                dusk="rx-apply-template"
                                title="{{ $t['description'] }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-zinc-800 text-emerald-700 dark:text-emerald-300 text-sm font-medium hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition disabled:opacity-40"
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

            {{-- Lista de medicamentos --}}
            <div class="border-t border-gray-100 dark:border-zinc-800 pt-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                    Medicamentos
                    @if (count($items) > 0)
                        <span class="ml-1 text-xs font-normal text-gray-400">({{ count($items) }})</span>
                    @endif
                </h3>

                @if (count($items) > 0)
                    <div class="space-y-2 mb-4">
                        @foreach ($items as $index => $item)
                            <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg px-4 py-3" dusk="rx-item">
                                @if ($item['editing'])
                                    {{-- Modo edición inline --}}
                                    <div class="space-y-2">
                                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                                            <div class="lg:col-span-2">
                                                <input
                                                    wire:model="items.{{ $index }}.medication_name"
                                                    type="text"
                                                    placeholder="Medicamento *"
                                                    class="w-full px-2.5 py-1.5 border border-emerald-400 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-emerald-500"
                                                />
                                            </div>
                                            <div>
                                                <input
                                                    wire:model="items.{{ $index }}.dose"
                                                    type="text"
                                                    placeholder="Dosis *"
                                                    class="w-full px-2.5 py-1.5 border border-emerald-400 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-emerald-500"
                                                />
                                            </div>
                                            <div>
                                                <input
                                                    wire:model="items.{{ $index }}.frequency"
                                                    type="text"
                                                    placeholder="Frecuencia *"
                                                    class="w-full px-2.5 py-1.5 border border-emerald-400 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-emerald-500"
                                                />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input
                                                wire:model="items.{{ $index }}.duration"
                                                type="text"
                                                placeholder="Duración *"
                                                class="w-full px-2.5 py-1.5 border border-emerald-400 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-emerald-500"
                                            />
                                            <input
                                                wire:model="items.{{ $index }}.instructions"
                                                type="text"
                                                placeholder="Instrucciones (opcional)"
                                                class="w-full px-2.5 py-1.5 border border-gray-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-emerald-500"
                                            />
                                        </div>
                                        <div class="flex justify-end gap-2 pt-1">
                                            <button
                                                wire:click="cancelEditing('{{ $item['id'] }}')"
                                                class="px-3 py-1 text-xs rounded-md border border-gray-300 dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition"
                                            >
                                                Cancelar
                                            </button>
                                            <button
                                                wire:click="saveItem('{{ $item['id'] }}')"
                                                class="px-3 py-1 text-xs rounded-md bg-emerald-600 text-white hover:bg-emerald-700 transition"
                                            >
                                                Guardar
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    {{-- Vista normal --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $item['medication_name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $item['dose'] }} · {{ $item['frequency'] }} ·
                                                {{ $item['duration'] }}
                                            </p>
                                            @if ($item['instructions'])
                                                <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5 italic">
                                                    {{ $item['instructions'] }}
                                                </p>
                                            @endif
                                        </div>
                                        @if (! $finalized)
                                            <div class="flex-shrink-0 flex items-center gap-1">
                                                <button
                                                    wire:click="startEditing('{{ $item['id'] }}')"
                                                    dusk="rx-edit-item"
                                                    class="text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition p-1"
                                                    title="Editar"
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
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                        />
                                                    </svg>
                                                </button>
                                                <button
                                                    wire:click="removeItem('{{ $item['id'] }}')"
                                                    wire:loading.attr="disabled"
                                                    dusk="rx-remove-item"
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
                        Sin medicamentos aún. Aplica una plantilla o agrega uno manualmente.
                    </p>
                @endif

                {{-- Formulario agregar medicamento libre --}}
                @if (! $finalized)
                    @if ($prescriptionId)
                        <div
                            class="bg-gray-50 dark:bg-zinc-800/60 rounded-xl p-4 border border-gray-200 dark:border-zinc-700"
                        >
                            <p
                                class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3"
                            >
                                Agregar Medicamento Libre
                            </p>
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                                <div class="lg:col-span-2">
                                    <input
                                        wire:model="newMedicationName"
                                        type="text"
                                        placeholder="Medicamento *"
                                        dusk="rx-new-medication"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                    @error('newMedicationName')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <input
                                        wire:model="newDose"
                                        type="text"
                                        placeholder="Dosis *"
                                        dusk="rx-new-dose"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                    @error('newDose')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <input
                                        wire:model="newFrequency"
                                        type="text"
                                        placeholder="Frecuencia *"
                                        dusk="rx-new-frequency"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                    @error('newFrequency')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <input
                                        wire:model="newDuration"
                                        type="text"
                                        placeholder="Duración *"
                                        dusk="rx-new-duration"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                    @error('newDuration')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <input
                                        wire:model="newInstructions"
                                        type="text"
                                        placeholder="Instrucciones (opcional)"
                                        dusk="rx-new-instructions"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button
                                    wire:click="addItem"
                                    wire:loading.attr="disabled"
                                    dusk="rx-add-item-btn"
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white dark:bg-zinc-700 border border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-sm font-medium hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition disabled:opacity-50"
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
                            Escribe una observación o aplica una plantilla para habilitar la edición de medicamentos.
                        </p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
