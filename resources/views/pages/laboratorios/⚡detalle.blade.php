<?php

use Livewire\Component;
use App\Contracts\LaboratoryItemResultServiceContract;
use App\Models\LaboratoryRequest;

new class extends Component {
    public LaboratoryRequest $laboratoryRequest;

    public array $newResults = [];
    public string $errorMessage = '';

    public function mount(LaboratoryRequest $laboratorio): void
    {
        $patient = $laboratorio->consultation?->patient;

        abort_unless(
            $patient !== null &&
                auth()
                    ->user()
                    ?->can('view', $patient),
            403,
        );

        $laboratorio->load(['consultation.patient', 'consultation.doctor', 'items.results', 'attachments']);
        $this->laboratoryRequest = $laboratorio;
    }

    public function saveResult(string $itemId): void
    {
        $this->errorMessage = '';
        $data = $this->newResults[$itemId] ?? [];

        if (empty($data['value']) && empty($data['report'])) {
            return;
        }

        try {
            app(LaboratoryItemResultServiceContract::class)->create(
                $itemId,
                [
                    'value' => ! empty($data['value']) ? trim((string) $data['value']) : null,
                    'report_text' => ! empty($data['report']) ? trim((string) $data['report']) : null,
                    'is_abnormal' => ! empty($data['abnormal']),
                ],
                $this->laboratoryRequest->consultation_id,
            );

            $this->newResults[$itemId] = [];
            $this->laboratoryRequest->load([
                'consultation.patient',
                'consultation.doctor',
                'items.results',
                'attachments',
            ]);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar: ' . $e->getMessage();
        }
    }

    public function deleteResult(string $resultId): void
    {
        $this->errorMessage = '';
        try {
            app(LaboratoryItemResultServiceContract::class)->delete($resultId);
            $this->laboratoryRequest->load([
                'consultation.patient',
                'consultation.doctor',
                'items.results',
                'attachments',
            ]);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar: ' . $e->getMessage();
        }
    }

    public function markReceived(): void
    {
        $this->errorMessage = '';
        try {
            $this->laboratoryRequest->update(['status' => 'received']);
            $this->laboratoryRequest->refresh();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error: ' . $e->getMessage();
        }
    }
};
?>

<div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:button
                    href="{{ route('pacientes.laboratorios', $laboratoryRequest->consultation->patient) }}"
                    variant="subtle"
                    size="sm"
                    icon="arrow-left"
                    class="mb-2"
                >
                    Volver a Laboratorios
                </flux:button>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Detalle de Laboratorio</h1>
                <p class="text-sm text-zinc-500">
                    Solicitado el {{ $laboratoryRequest->created_at->isoFormat('D [de] MMMM YYYY') }} · Dr.
                    {{ $laboratoryRequest->consultation->doctor?->full_name ?? 'Desconocido' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if ($laboratoryRequest->status === 'pending')
                    @php
                        $hasResults = $laboratoryRequest->items->contains(fn ($i) => $i->results->isNotEmpty());
                    @endphp

                    @if ($hasResults)
                        <button
                            wire:click="markReceived"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium transition disabled:opacity-50"
                        >
                            <flux:icon.check class="size-4" />
                            Marcar como recibido
                        </button>
                    @endif
                @endif

                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold @if($laboratoryRequest->status === 'received') bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 @else bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 @endif"
                >
                    @if ($laboratoryRequest->status === 'received')
                        <flux:icon.check-circle class="size-4" />
                        Resultados Recibidos
                    @else
                        <flux:icon.clock class="size-4" />
                        Pendiente
                    @endif
                </span>
            </div>
        </div>

        @if ($errorMessage)
            <div
                class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300"
            >
                {{ $errorMessage }}
            </div>
        @endif

        <div
            class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden"
        >
            <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <h2 class="text-base font-bold text-zinc-800 dark:text-zinc-100">
                    {{ $laboratoryRequest->items->first()?->exam_name ?? 'Examen' }}
                </h2>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($laboratoryRequest->items as $item)
                    <div class="p-4 space-y-3">
                        {{-- Nombre del parámetro --}}
                        @if ($item->parameter_name)
                            <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $item->parameter_name }}
                            </p>
                        @endif

                        {{-- Resultados existentes (siempre visibles) --}}
                        @if ($item->results->isNotEmpty())
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr
                                            class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wide"
                                        >
                                            <th class="px-3 py-1.5 text-left font-medium">Valor</th>
                                            <th class="px-3 py-1.5 text-center font-medium w-8">⚠</th>
                                            @if ($laboratoryRequest->status === 'pending')
                                                <th class="w-6"></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        @foreach ($item->results as $result)
                                            <tr class="bg-white dark:bg-zinc-900">
                                                <td
                                                    class="px-3 py-2 font-medium {{ $result->is_abnormal ? 'text-red-600 dark:text-red-400' : 'text-zinc-800 dark:text-zinc-200' }}"
                                                >
                                                    {{ $result->value ?: '—' }}
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    @if ($result->is_abnormal)
                                                        <flux:icon.exclamation-triangle
                                                            class="size-3.5 text-red-500 mx-auto"
                                                        />
                                                    @else
                                                        <flux:icon.check class="size-3.5 text-green-500 mx-auto" />
                                                    @endif
                                                </td>
                                                @if ($laboratoryRequest->status === 'pending')
                                                    <td class="px-2 py-2">
                                                        <button
                                                            wire:click="deleteResult('{{ $result->id }}')"
                                                            class="text-red-400 hover:text-red-600 transition"
                                                            title="Eliminar"
                                                        >
                                                            <flux:icon.x-mark class="size-3.5" />
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>
                                            @if ($result->report_text)
                                                <tr class="bg-zinc-50 dark:bg-zinc-800/50">
                                                    <td
                                                        colspan="{{ $laboratoryRequest->status === 'pending' ? 3 : 2 }}"
                                                        class="px-3 py-2 text-xs text-zinc-600 dark:text-zinc-400 italic"
                                                    >
                                                        {{ $result->report_text }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Formulario para agregar resultado: solo cuando pendiente --}}
                        @if ($laboratoryRequest->status === 'pending')
                            <div
                                class="rounded-lg border border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-900/10 p-3 space-y-2"
                            >
                                <input
                                    wire:model="newResults.{{ $item->id }}.value"
                                    type="text"
                                    placeholder="Valor"
                                    class="w-full px-2.5 py-1.5 border border-gray-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-1 focus:ring-sky-500"
                                />
                                <textarea
                                    wire:model="newResults.{{ $item->id }}.report"
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
                                            wire:model="newResults.{{ $item->id }}.abnormal"
                                            class="rounded border-gray-300 text-red-500 focus:ring-red-400"
                                        />
                                        Resultado anormal
                                    </label>
                                    <button
                                        wire:click="saveResult('{{ $item->id }}')"
                                        wire:loading.attr="disabled"
                                        class="px-3 py-1 text-xs rounded bg-sky-600 hover:bg-sky-700 text-white transition disabled:opacity-50"
                                    >
                                        Guardar
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @if ($laboratoryRequest->attachments->isNotEmpty())
            <div
                class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6"
            >
                <h2 class="text-lg font-bold mb-4 text-zinc-800 dark:text-zinc-100 flex items-center gap-2">
                    <flux:icon.paper-clip class="size-5" />
                    Documentos Adjuntos
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($laboratoryRequest->attachments as $att)
                        <a
                            href="{{ Storage::url($att->file_path) }}"
                            target="_blank"
                            class="flex items-center gap-3 p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition group"
                        >
                            <div
                                class="p-2 bg-white dark:bg-zinc-900 rounded-lg shadow-sm group-hover:shadow text-teal-600 dark:text-teal-400"
                            >
                                @if (Str::endsWith(strtolower($att->file_path), '.pdf'))
                                    <flux:icon.document-text class="size-6" />
                                @else
                                    <flux:icon.photo class="size-6" />
                                @endif
                            </div>
                            <div class="overflow-hidden">
                                <p
                                    class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate"
                                    title="{{ $att->original_name }}"
                                >
                                    {{ $att->original_name ?? 'Documento' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
