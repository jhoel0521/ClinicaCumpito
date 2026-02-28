<x-layouts::app :title="'Consulta — ' . $consultation->patient->full_name">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Flash --}}
        @if (session('success'))
            <div
                class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300"
            >
                {{ session('success') }}
            </div>
        @endif

        {{--
            ══════════════════════════════════════════
            HEADER ESTÁTICO: Información de la consulta
            ══════════════════════════════════════════
        --}}
        <div
            class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm mb-6 overflow-hidden"
        >
            {{-- Banner superior --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-800 dark:to-blue-950 px-6 py-5">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <a
                            href="{{ route('pacientes.show', $consultation->patient->id) }}"
                            class="text-blue-200 hover:text-white text-sm transition mb-1 inline-flex items-center gap-1"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                />
                            </svg>
                            {{ $consultation->patient->full_name }}
                        </a>
                        <h1 class="text-xl font-bold text-white">Consulta Médica</h1>
                        <p class="text-blue-200 text-sm mt-0.5">
                            {{ optional($consultation->consultation_date)->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @php
                            $status = is_object($consultation->status) ? $consultation->status->value() : $consultation->status;
                            $statusColors = [
                                'draft' => 'bg-gray-400',
                                'saved' => 'bg-yellow-400',
                                'finalized' => 'bg-green-400',
                            ];
                            $statusLabels = [
                                'draft' => 'Borrador',
                                'saved' => 'Guardada',
                                'finalized' => 'Finalizada',
                            ];
                        @endphp

                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/20 text-white border border-white/30"
                        >
                            <span class="w-2 h-2 rounded-full {{ $statusColors[$status] ?? 'bg-gray-400' }}"></span>
                            {{ $statusLabels[$status] ?? $status }}
                        </span>

                        @if ($status !== 'finalized')
                            <a
                                href="{{ route('consultas.edit', $consultation->id) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/20 hover:bg-white/30 text-white border border-white/30 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                                Editar consulta
                            </a>
                        @endif

                        <a
                            href="{{ route('consultas.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 hover:bg-white/20 text-blue-200 border border-white/20 transition"
                        >
                            Todas las consultas
                        </a>
                    </div>
                </div>
            </div>

            {{-- Meta info --}}
            <div
                class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-gray-100 dark:divide-zinc-800"
            >
                <div class="px-5 py-4">
                    <p class="text-xs font-medium text-gray-400 dark:text-zinc-500 uppercase tracking-wide mb-1">
                        Doctor
                    </p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                        {{ $consultation->doctor->full_name }}
                    </p>
                </div>
                <div class="px-5 py-4">
                    <p class="text-xs font-medium text-gray-400 dark:text-zinc-500 uppercase tracking-wide mb-1">
                        Tipo
                    </p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ is_object($consultation->type) ? $consultation->type->value() : $consultation->type }}
                    </p>
                </div>
                <div class="px-5 py-4">
                    <p class="text-xs font-medium text-gray-400 dark:text-zinc-500 uppercase tracking-wide mb-1">
                        Paciente
                    </p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                        {{ $consultation->patient->full_name }}
                    </p>
                </div>
                <div class="px-5 py-4">
                    <p class="text-xs font-medium text-gray-400 dark:text-zinc-500 uppercase tracking-wide mb-1">
                        Fecha
                    </p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ optional($consultation->consultation_date)->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Visor de consulta escaneada (si existe) --}}
        @if ($consultation->hasScannedFile())
            <div class="mb-6 rounded-xl border border-amber-200 dark:border-amber-800 overflow-hidden">
                <div class="bg-amber-50 dark:bg-amber-900/20 px-4 py-2 flex items-center justify-between">
                    <span class="text-sm font-medium text-amber-700 dark:text-amber-300">
                        Documento escaneado: {{ $consultation->scanned_file_name }}
                    </span>
                    <a
                        href="{{ route('consultas.archivo.serve', $consultation->id) }}"
                        target="_blank"
                        class="text-xs text-amber-600 dark:text-amber-400 hover:underline"
                    >
                        Abrir en nueva pestaña →
                    </a>
                </div>

                @if ($consultation->isScannedPdf())
                    <embed
                        src="{{ route('consultas.archivo.serve', $consultation->id) }}"
                        type="application/pdf"
                        class="w-full"
                        style="height: 420px"
                    />
                @else
                    <div class="p-4 bg-white dark:bg-zinc-900 text-center">
                        <img
                            src="{{ route('consultas.archivo.serve', $consultation->id) }}"
                            alt="Consulta escaneada"
                            class="max-h-96 mx-auto rounded"
                        />
                    </div>
                @endif
            </div>
        @endif

        {{--
            ══════════════════════════════════
            BARRA DE ANCLAJE (scroll navigation)
            ══════════════════════════════════
        --}}
        <nav
            class="sticky top-0 z-20 bg-white/95 dark:bg-zinc-950/95 backdrop-blur border-b border-gray-200 dark:border-zinc-800 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 mb-6"
        >
            <div class="flex items-center gap-1 overflow-x-auto py-2 scrollbar-none">
                @foreach ([
                        ['href' => '#signos-vitales', 'label' => 'Signos Vitales'],
                        ['href' => '#soap', 'label' => 'SOAP'],
                        ['href' => '#receta', 'label' => 'Receta'],
                        ['href' => '#laboratorio', 'label' => 'Laboratorio'],
                        ['href' => '#vacunas', 'label' => 'Vacunas']
                    ]
                    as $link)
                    <a
                        href="{{ $link['href'] }}"
                        class="flex-shrink-0 px-3 py-1.5 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 font-medium transition whitespace-nowrap"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        {{--
            ══════════════════════════════════
            SECCIONES LIVEWIRE REACTIVAS
            ══════════════════════════════════
        --}}
        <div class="space-y-6">
            {{-- 1. Signos Vitales --}}
            <livewire:consultation-vital-signs :consultationId="$consultation->id" />

            {{-- 2. Nota SOAP --}}
            <livewire:consultation-soap-note :consultationId="$consultation->id" />

            {{-- 3. Receta --}}
            <livewire:consultation-prescription :consultationId="$consultation->id" />

            {{-- 4. Laboratorio --}}
            <livewire:consultation-laboratory :consultationId="$consultation->id" />

            {{-- 5. Vacunas --}}
            <livewire:consultation-vaccines :consultationId="$consultation->id" />
        </div>

        {{-- Pie de página --}}
        <div
            class="mt-8 pt-4 border-t border-gray-100 dark:border-zinc-800 flex justify-between items-center text-xs text-gray-400 dark:text-zinc-600"
        >
            <span>Consulta #{{ substr($consultation->id, 0, 8) }}</span>
            <a
                href="{{ route('pacientes.show', $consultation->patient->id) }}"
                class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 transition"
            >
                Ver dashboard del paciente →
            </a>
        </div>
    </div>
</x-layouts::app>
