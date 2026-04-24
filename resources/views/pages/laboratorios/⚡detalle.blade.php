<?php

use Livewire\Component;
use App\Models\LaboratoryRequest;

new class extends Component {
    public LaboratoryRequest $laboratoryRequest;

    public function mount(LaboratoryRequest $laboratoryRequest)
    {
        $laboratoryRequest->load(['patient', 'consultation.doctor', 'items.results', 'attachments']);
        $this->laboratoryRequest = $laboratoryRequest;
    }
};
?>

<div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:button
                    href="{{ route('pacientes.feed', $laboratoryRequest->patient) }}"
                    variant="subtle"
                    size="sm"
                    icon="arrow-left"
                    class="mb-2"
                >
                    Volver al Historial
                </flux:button>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Orden de Laboratorio</h1>
                <p class="text-sm text-zinc-500">
                    Solicitada el {{ $laboratoryRequest->created_at->isoFormat('D [de] MMMM YYYY') }} por Dr.
                    {{ $laboratoryRequest->consultation->doctor?->full_name ?? 'Desconocido' }}
                </p>
            </div>

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

        <div class="bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6">
            <h2
                class="text-lg font-bold mb-4 text-zinc-800 dark:text-zinc-100 border-b border-zinc-100 dark:border-zinc-800 pb-2"
            >
                Resultados
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-600 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-3 font-medium rounded-tl-lg">Examen / Parámetro</th>
                            <th class="px-4 py-3 font-medium text-right rounded-tr-lg">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($laboratoryRequest->items as $item)
                            @if ($item->results->isNotEmpty())
                                @foreach ($item->results as $result)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                                        <td class="px-4 py-3 text-zinc-900 dark:text-zinc-200">
                                            <span class="font-medium">{{ $item->exam_name }}</span>
                                            @if ($result->parameter_name)
                                                <span class="text-zinc-500 ml-1">({{ $result->parameter_name }})</span>
                                            @endif

                                            @if ($result->report_text)
                                                <div
                                                    class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 p-2 rounded"
                                                >
                                                    {{ $result->report_text }}
                                                </div>
                                            @endif
                                        </td>
                                        <td
                                            class="px-4 py-3 font-mono text-right text-zinc-900 dark:text-zinc-200 text-lg"
                                        >
                                            {{ $result->value ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition opacity-70">
                                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-200">
                                        {{ $item->exam_name }}
                                    </td>
                                    <td class="px-4 py-3 text-right italic text-zinc-500">Sin cargar</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
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
                                <p class="text-xs text-zinc-500 truncate">
                                    {{ $att->exam_name ?? 'General' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
