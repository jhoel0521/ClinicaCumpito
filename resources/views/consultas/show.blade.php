<x-layouts::app :title="'Consulta — ' . $consultation->patient->full_name">
    {{-- Header Livewire: estado, fecha editable, transición finalizar --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        @if (session('success'))
            <div
                class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300"
            >
                {{ session('success') }}
            </div>
        @endif

        <livewire:consultation-header :consultationId="$consultation->id" />
    </div>

    {{-- Nav sticky con íconos (PediaSOAP style) --}}
    <nav
        class="sticky top-0 z-20 bg-white/95 dark:bg-zinc-950/95 backdrop-blur border-b border-gray-200 dark:border-zinc-800"
    >
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-1 overflow-x-auto py-2 scrollbar-none">
                <a
                    href="#signos-vitales"
                    class="flex items-center gap-1.5 flex-shrink-0 px-3 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:text-green-700 hover:bg-green-50 dark:hover:text-green-300 dark:hover:bg-green-950/40 transition whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        />
                    </svg>
                    <span class="hidden sm:inline">Signos Vitales</span>
                    <span class="sm:hidden">Vitales</span>
                </a>

                <a
                    href="#soap"
                    class="flex items-center gap-1.5 flex-shrink-0 px-3 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:text-blue-300 dark:hover:bg-blue-950/40 transition whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    SOAP
                </a>

                <a
                    href="#receta"
                    class="flex items-center gap-1.5 flex-shrink-0 px-3 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:text-emerald-300 dark:hover:bg-emerald-950/40 transition whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                        />
                    </svg>
                    Receta
                </a>

                <a
                    href="#laboratorio"
                    class="flex items-center gap-1.5 flex-shrink-0 px-3 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:text-purple-700 hover:bg-purple-50 dark:hover:text-purple-300 dark:hover:bg-purple-950/40 transition whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                        />
                    </svg>
                    Laboratorio
                </a>

                <a
                    href="#vacunas"
                    class="flex items-center gap-1.5 flex-shrink-0 px-3 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:text-orange-700 hover:bg-orange-50 dark:hover:text-orange-300 dark:hover:bg-orange-950/40 transition whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        />
                    </svg>
                    Vacunas
                </a>
            </div>
        </div>
    </nav>

    {{-- Visor de consulta escaneada (si existe) --}}
    @if ($consultation->hasScannedFile())
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="rounded-xl border border-amber-200 dark:border-amber-800 overflow-hidden">
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
        </div>
    @endif

    {{-- Secciones principales --}}
    <main class="pb-24">
        {{-- Signos Vitales --}}
        <section class="py-10 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="md:w-72 flex-shrink-0">
                        <div class="sticky top-16 space-y-2">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-11 h-11 bg-green-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-green-500/25"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                        />
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Signos Vitales</h2>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                Peso, talla, perímetro cefálico y temperatura. Se guardan automáticamente al cambiar
                                cada campo.
                            </p>
                        </div>
                    </div>
                    <div class="flex-1">
                        <livewire:consultation-vital-signs :consultationId="$consultation->id" />
                    </div>
                </div>
            </div>
        </section>

        {{-- SOAP --}}
        <section
            class="py-10 px-4 sm:px-6 lg:px-8 bg-blue-50/40 dark:bg-blue-950/10 border-y border-blue-100 dark:border-blue-900/30"
        >
            <div class="max-w-5xl mx-auto">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="md:w-72 flex-shrink-0">
                        <div class="sticky top-16 space-y-2">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-11 h-11 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-600/25 text-lg font-bold"
                                >
                                    S·O·A·P
                                </div>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Nota SOAP</h2>
                            <p class="text-sm text-blue-700/70 dark:text-blue-300/60 leading-relaxed">
                                Subjetivo, Objetivo, Análisis y Plan. Cada campo se guarda automáticamente al salir.
                            </p>
                        </div>
                    </div>
                    <div class="flex-1">
                        <livewire:consultation-soap-note :consultationId="$consultation->id" />
                    </div>
                </div>
            </div>
        </section>

        {{-- Receta --}}
        <section class="py-10 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="md:w-72 flex-shrink-0">
                        <div class="sticky top-16 space-y-2">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-11 h-11 bg-emerald-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/25"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                        />
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Receta Médica</h2>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                Medicamentos con dosis, frecuencia y duración. Podés usar una plantilla o agregar uno
                                por uno.
                            </p>
                            @if ($consultation->prescriptions->isNotEmpty())
                                <div class="mt-3 space-y-1.5">
                                    <a
                                        href="{{ route('consultas.pdf.recetas.all', $consultation) }}"
                                        target="_blank"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                            />
                                        </svg>
                                        Imprimir todas
                                    </a>
                                    @foreach ($consultation->prescriptions as $rx)
                                        <a
                                            href="{{ route('consultas.pdf.recetas.single', [$consultation, $rx]) }}"
                                            target="_blank"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 transition"
                                        >
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                                />
                                            </svg>
                                            {{ $rx->reason ?: 'Receta #' . ($loop->index + 1) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1">
                        <livewire:consultation-prescription :consultationId="$consultation->id" />
                    </div>
                </div>
            </div>
        </section>

        {{-- Laboratorio --}}
        <section
            class="py-10 px-4 sm:px-6 lg:px-8 bg-purple-50/40 dark:bg-purple-950/10 border-y border-purple-100 dark:border-purple-900/30"
        >
            <div class="max-w-5xl mx-auto">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="md:w-72 flex-shrink-0">
                        <div class="sticky top-16 space-y-2">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-11 h-11 bg-purple-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-purple-600/25"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                                        />
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Laboratorio</h2>
                            </div>
                            <p class="text-sm text-purple-700/70 dark:text-purple-300/60 leading-relaxed">
                                Estudios solicitados. Podés usar una plantilla o agregar exámenes individualmente.
                            </p>
                            @if ($consultation->laboratoryRequests->isNotEmpty())
                                <div class="mt-3 space-y-1.5">
                                    <a
                                        href="{{ route('consultas.pdf.laboratorio.all', $consultation) }}"
                                        target="_blank"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-purple-100 transition"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                            />
                                        </svg>
                                        Imprimir todos
                                    </a>
                                    @foreach ($consultation->laboratoryRequests as $lab)
                                        <a
                                            href="{{ route('consultas.pdf.laboratorio.single', [$consultation, $lab]) }}"
                                            target="_blank"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 transition"
                                        >
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                                />
                                            </svg>
                                            Solicitud #{{ $loop->index + 1 }}
                                            @if ($lab->status === 'received')
                                                <span class="ml-1 text-emerald-600 dark:text-emerald-400">
                                                    (con resultados)
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1">
                        <livewire:consultation-laboratory :consultationId="$consultation->id" />
                    </div>
                </div>
            </div>
        </section>

        {{-- Vacunas --}}
        <section class="py-10 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="md:w-72 flex-shrink-0">
                        <div class="sticky top-16 space-y-2">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-11 h-11 bg-orange-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-orange-500/25"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                        />
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Vacunas</h2>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                Registro de vacunas aplicadas en esta consulta.
                            </p>
                        </div>
                    </div>
                    <div class="flex-1">
                        <livewire:consultation-vaccines :consultationId="$consultation->id" />
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- Footer --}}
    <div class="border-t border-gray-100 dark:border-zinc-800 bg-white dark:bg-zinc-950">
        <div
            class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center text-xs text-gray-400 dark:text-zinc-600"
        >
            <span>Consulta #{{ substr($consultation->id, 0, 8) }}</span>
            <a
                href="{{ route('pacientes.show', $consultation->patient) }}"
                class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 transition"
            >
                Ver dashboard del paciente →
            </a>
        </div>
    </div>
</x-layouts::app>
