<x-layouts::app :title="$patient->full_name">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
        {{-- NAVEGACIÓN INTERNA (anchor links) --}}
        <nav
            class="sticky top-0 z-10 bg-white/90 dark:bg-zinc-900/90 backdrop-blur border-b border-zinc-200 dark:border-zinc-700 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-2 flex gap-4 text-sm overflow-x-auto"
        >
            <a href="#datos-base" class="text-teal-600 dark:text-teal-400 hover:underline whitespace-nowrap">
                Datos Base
            </a>
            <span class="text-zinc-300 dark:text-zinc-600">·</span>
            <a href="#ultima-consulta" class="text-teal-600 dark:text-teal-400 hover:underline whitespace-nowrap">
                Última Consulta
            </a>
            <span class="text-zinc-300 dark:text-zinc-600">·</span>
            <a href="#graficas-oms" class="text-teal-600 dark:text-teal-400 hover:underline whitespace-nowrap">
                Gráficas OMS
            </a>
            <span class="text-zinc-300 dark:text-zinc-600">·</span>
            <a href="#historial-consultas" class="text-teal-600 dark:text-teal-400 hover:underline whitespace-nowrap">
                Consultas
            </a>
            <span class="text-zinc-300 dark:text-zinc-600">·</span>
            <a href="#historial-recetas" class="text-teal-600 dark:text-teal-400 hover:underline whitespace-nowrap">
                Recetas
            </a>
            <span class="text-zinc-300 dark:text-zinc-600">·</span>
            <a
                href="#historial-laboratorios"
                class="text-teal-600 dark:text-teal-400 hover:underline whitespace-nowrap"
            >
                Laboratorios
            </a>
        </nav>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- SECCIÓN 1: DATOS BASE --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <section id="datos-base" dusk="section-datos-base" class="scroll-mt-16">
            {{-- Encabezado del paciente --}}
            <div
                class="bg-gradient-to-r from-teal-600 to-teal-700 dark:from-teal-800 dark:to-teal-900 text-white rounded-2xl shadow-lg p-6 mb-6"
            >
                <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $patient->full_name }}</h1>
                        <div class="flex flex-wrap gap-3 mt-3">
                            <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-medium">
                                {{ $patient->age()?->forDisplay() ?? '—' }}
                            </span>
                            <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-medium">
                                {{ $patient->gender ? ($patient->gender->value() === 'M' ? 'Masculino' : 'Femenino') : '—' }}
                            </span>
                            @if ($patient->blood_group)
                                <span class="bg-red-500/80 px-3 py-1 rounded-full text-sm font-medium">
                                    {{ $patient->blood_group }}
                                </span>
                            @endif

                            <span class="bg-white/10 px-3 py-1 rounded-full text-xs text-teal-100">
                                Nac. {{ $patient->date_of_birth?->format('d/m/Y') ?? '—' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        @if ($patient->hasCompleteBasicData())
                            <a
                                href="{{ route('consultas.create') }}"
                                class="inline-flex items-center gap-1.5 bg-white text-teal-700 hover:bg-teal-50 font-semibold px-4 py-2 rounded-lg text-sm transition"
                            >
                                + Nueva Consulta
                            </a>
                        @else
                            <a
                                href="{{ route('pacientes.edit', $patient->id) }}?require_complete=1"
                                class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-400 text-white font-semibold px-4 py-2 rounded-lg text-sm transition"
                                title="Completa los datos básicos del paciente antes de crear una consulta"
                            >
                                ⚠ Completar datos del paciente
                            </a>
                        @endif
                        <a
                            href="{{ route('pacientes.edit', $patient->id) }}"
                            class="inline-flex items-center gap-1.5 bg-white/20 hover:bg-white/30 font-medium px-4 py-2 rounded-lg text-sm transition"
                        >
                            Editar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Grid de datos clínicos --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Antecedentes --}}
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-3">
                        Antecedentes
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <p class="text-xs text-red-500 font-medium uppercase">Alérgicos</p>
                            <p class="text-zinc-700 dark:text-zinc-300 mt-0.5">
                                {{ $patient->allergies ?: '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-amber-500 font-medium uppercase">Patológicos</p>
                            <p class="text-zinc-700 dark:text-zinc-300 mt-0.5">
                                {{ $patient->pathologies ?: '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-500 font-medium uppercase">Quirúrgicos</p>
                            <p class="text-zinc-700 dark:text-zinc-300 mt-0.5">
                                {{ $patient->surgeries ?: '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Screening / Condiciones médicas --}}
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-3">
                        Screening
                    </h3>
                    @if ($patient->medicalConditions->isNotEmpty())
                        <ul class="space-y-1.5 text-sm">
                            @foreach ($patient->medicalConditions as $condition)
                                <li class="flex items-center justify-between gap-2">
                                    <span class="text-zinc-700 dark:text-zinc-300 truncate">
                                        {{ $condition->name }}
                                    </span>
                                    @php
                                        $status = strtolower(str_replace(' ', '_', $condition->pivot->status ?? ''));
                                    @endphp

                                    <span
                                        @class([
                                            'inline-block px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0',
                                            'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $status === 'negative',
                                            'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' => $status === 'positive',
                                            'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' => ! in_array($status, ['positive', 'negative']),
                                        ])
                                    >
                                        {{ match ($status) {'positive' => 'Positivo','negative' => 'Negativo', default => '?',} }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-zinc-400 dark:text-zinc-500 text-sm italic">Sin condiciones registradas</p>
                    @endif
                </div>

                {{-- Datos al nacer --}}
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-3">
                        Datos al Nacer
                    </h3>
                    <dl class="space-y-2 text-sm">
                        @if ($patient->birth_weight)
                            <div class="flex justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Peso</dt>
                                <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $patient->birth_weight }} kg
                                </dd>
                            </div>
                        @endif

                        @if ($patient->birth_height)
                            <div class="flex justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Talla</dt>
                                <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $patient->birth_height }} cm
                                </dd>
                            </div>
                        @endif

                        @if ($patient->birth_head_circumference)
                            <div class="flex justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Cef.</dt>
                                <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $patient->birth_head_circumference }} cm
                                </dd>
                            </div>
                        @endif

                        @if ($patient->birth_type)
                            <div class="flex justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Tipo de parto</dt>
                                <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ ucfirst($patient->birth_type->value()) }}
                                </dd>
                            </div>
                        @endif

                        @if ($patient->birth_place)
                            <div class="flex justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Lugar</dt>
                                <dd class="font-medium text-zinc-800 dark:text-zinc-200 truncate max-w-28">
                                    {{ $patient->birth_place }}
                                </dd>
                            </div>
                        @endif

                        @if (! $patient->birth_weight && ! $patient->birth_height && ! $patient->birth_head_circumference)
                            <p class="text-zinc-400 dark:text-zinc-500 italic">Sin datos registrados</p>
                        @endif
                    </dl>
                </div>
            </div>
        </section>

        <hr class="border-zinc-200 dark:border-zinc-800" />

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- SECCIÓN 2: ÚLTIMA CONSULTA --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <section id="ultima-consulta" dusk="section-ultima-consulta" class="scroll-mt-16">
            <h2 class="text-xl font-bold text-zinc-800 dark:text-zinc-100 mb-4">Última Consulta</h2>

            @if ($latestConsultation)
                {{-- Cabecera de la consulta --}}
                <div class="flex flex-wrap gap-4 items-center mb-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <span>📅 {{ $latestConsultation->consultation_date->format('d/m/Y H:i') }}</span>
                    @if ($latestConsultation->doctor)
                        <span>👨‍⚕️ {{ $latestConsultation->doctor->full_name }}</span>
                    @endif

                    <span
                        class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300"
                    >
                        {{ strtoupper($latestConsultation->status->value()) }}
                    </span>
                    <a
                        href="{{ route('consultas.show', $latestConsultation->id) }}"
                        class="ml-auto text-teal-600 dark:text-teal-400 hover:underline font-medium"
                    >
                        Ver consulta completa →
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Signos Vitales --}}
                    @if ($latestConsultation->vitalSigns)
                        @php
                            $vs = $latestConsultation->vitalSigns;
                        @endphp

                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5"
                        >
                            <h3
                                class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-3"
                            >
                                Signos Vitales
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                @if ($vs->weight)
                                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center">
                                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                            {{ $vs->weight->value() }}
                                        </p>
                                        <p class="text-xs text-blue-500 dark:text-blue-400 mt-0.5">kg — Peso</p>
                                    </div>
                                @endif

                                @if ($vs->height)
                                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">
                                            {{ $vs->height->value() }}
                                        </p>
                                        <p class="text-xs text-green-500 dark:text-green-400 mt-0.5">cm — Talla</p>
                                    </div>
                                @endif

                                @if ($vs->head_circumference)
                                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 text-center">
                                        <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">
                                            {{ $vs->head_circumference->value() }}
                                        </p>
                                        <p class="text-xs text-purple-500 dark:text-purple-400 mt-0.5">
                                            cm — P. Cefálico
                                        </p>
                                    </div>
                                @endif

                                @if ($vs->temperature)
                                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3 text-center">
                                        <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">
                                            {{ $vs->temperature->value() }}
                                        </p>
                                        <p class="text-xs text-orange-500 dark:text-orange-400 mt-0.5">
                                            °C — Temperatura
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Nota SOAP --}}
                    @if ($latestConsultation->soapNote)
                        @php
                            $soap = $latestConsultation->soapNote;
                        @endphp

                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5"
                        >
                            <h3
                                class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-3"
                            >
                                Nota SOAP
                            </h3>
                            <div class="space-y-2 text-sm">
                                @foreach (['S' => 'Subjetivo', 'O' => 'Objetivo', 'A' => 'Análisis', 'P' => 'Plan'] as $key => $label)
                                    @php
                                        $value = match ($key) {
                                            'S' => $soap->subjective,
                                            'O' => $soap->objective,
                                            'A' => $soap->assessment,
                                            'P' => $soap->plan,
                                        };
                                    @endphp

                                    @if ($value)
                                        <div class="flex gap-2">
                                            <span
                                                class="flex-shrink-0 w-5 h-5 rounded bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 text-xs font-bold flex items-center justify-center"
                                            >
                                                {{ $key }}
                                            </span>
                                            <p class="text-zinc-700 dark:text-zinc-300 line-clamp-2">{{ $value }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Receta --}}
                    @if ($latestConsultation->prescription && $latestConsultation->prescription->items->isNotEmpty())
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5"
                        >
                            <h3
                                class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-3"
                            >
                                Receta
                            </h3>
                            <ul class="space-y-2 text-sm">
                                @foreach ($latestConsultation->prescription->items as $item)
                                    <li class="flex gap-2 items-start">
                                        <span class="text-teal-500 mt-0.5">💊</span>
                                        <div>
                                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                                {{ $item->medication_name }}
                                            </span>
                                            <span class="text-zinc-500 dark:text-zinc-400">
                                                — {{ $item->dose }} · {{ $item->frequency }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Laboratorio --}}
                    @if ($latestConsultation->laboratoryRequest && $latestConsultation->laboratoryRequest->items->isNotEmpty())
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5"
                        >
                            <h3
                                class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-3"
                            >
                                Laboratorio Solicitado
                            </h3>
                            <ul class="space-y-1.5 text-sm">
                                @foreach ($latestConsultation->laboratoryRequest->items as $item)
                                    <li class="flex gap-2 items-start">
                                        <span class="text-blue-500 mt-0.5">🧪</span>
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $item->exam_name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @else
                <div
                    class="bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-10 text-center"
                >
                    <p class="text-zinc-400 dark:text-zinc-500 text-sm">
                        Este paciente aún no tiene consultas registradas.
                    </p>
                    <a
                        href="{{ route('consultas.create') }}"
                        class="mt-3 inline-block text-teal-600 dark:text-teal-400 hover:underline text-sm font-medium"
                    >
                        Crear primera consulta →
                    </a>
                </div>
            @endif
        </section>

        <hr class="border-zinc-200 dark:border-zinc-800" />

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- SECCIÓN 3: GRÁFICAS OMS --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <section id="graficas-oms" dusk="growth-chart-panel" class="scroll-mt-16">
            <h2 class="text-xl font-bold text-zinc-800 dark:text-zinc-100 mb-4">Gráficas de Crecimiento OMS</h2>
            <livewire:patient-oms-chart :patientId="$patient->id" />
        </section>

        <hr class="border-zinc-200 dark:border-zinc-800" />

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- SECCIÓN 4: HISTORIAL DE CONSULTAS --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <section id="historial-consultas" dusk="section-historial-consultas" class="scroll-mt-16">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">Historial de Consultas</h2>
                <livewire:patient-upload-scan :patientId="$patient->id" />
            </div>

            @if ($patient->consultations->isNotEmpty())
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
                >
                    <table class="w-full text-sm">
                        <thead
                            class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wide"
                        >
                            <tr>
                                <th class="text-left px-4 py-3">Fecha</th>
                                <th class="text-left px-4 py-3">Doctor</th>
                                <th class="text-left px-4 py-3">Estado</th>
                                <th class="text-left px-4 py-3">Registros</th>
                                <th class="text-right px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($patient->consultations as $consultation)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                                    <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $consultation->consultation_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                        {{ $consultation->doctor?->full_name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            @class([
                                                'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                                                'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' =>
                                                    $consultation->status->value() === 'finalized',
                                                'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' => $consultation->status->value() === 'saved',
                                                'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' => $consultation->status->value() === 'draft',
                                            ])
                                        >
                                            {{ strtoupper($consultation->status->value()) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1.5 flex-wrap">
                                            @if ($consultation->scanned_file_path)
                                                <span
                                                    title="Consulta escaneada pendiente de digitalizar"
                                                    class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"
                                                >
                                                    Digitalizar
                                                </span>
                                            @endif

                                            @if ($consultation->vitalSigns)
                                                <span title="Signos Vitales" class="text-blue-400 text-xs">VS</span>
                                            @endif

                                            @if ($consultation->soapNote)
                                                <span title="SOAP" class="text-teal-400 text-xs">SOAP</span>
                                            @endif

                                            @if ($consultation->prescription)
                                                <span title="Receta" class="text-purple-400 text-xs">Rx</span>
                                            @endif

                                            @if ($consultation->laboratoryRequest)
                                                <span title="Laboratorio" class="text-orange-400 text-xs">Lab</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a
                                            href="{{ route('consultas.show', $consultation->id) }}"
                                            class="text-teal-600 dark:text-teal-400 hover:underline text-xs font-medium"
                                        >
                                            Ver →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-zinc-400 dark:text-zinc-500 text-sm italic">Sin consultas registradas.</p>
            @endif
        </section>

        <hr class="border-zinc-200 dark:border-zinc-800" />

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- SECCIÓN 5: HISTORIAL DE RECETAS --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <section id="historial-recetas" dusk="section-historial-recetas" class="scroll-mt-16">
            <h2 class="text-xl font-bold text-zinc-800 dark:text-zinc-100 mb-4">Historial de Recetas</h2>

            @php
                $consultasConReceta = $patient->consultations->filter(
                    fn ($c) => $c->prescription && $c->prescription->items->isNotEmpty(),
                );
            @endphp

            @if ($consultasConReceta->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($consultasConReceta as $consultation)
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex gap-3 items-center text-sm text-zinc-500 dark:text-zinc-400">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $consultation->consultation_date->format('d/m/Y') }}
                                    </span>
                                    <span>{{ $consultation->doctor?->full_name ?? '—' }}</span>
                                </div>
                                <a
                                    href="{{ route('consultas.show', $consultation->id) }}"
                                    class="text-xs text-teal-600 dark:text-teal-400 hover:underline"
                                >
                                    Ver consulta →
                                </a>
                            </div>
                            <ul class="space-y-1.5 text-sm">
                                @foreach ($consultation->prescription->items as $item)
                                    <li class="flex gap-3 items-start">
                                        <span class="text-purple-400 flex-shrink-0">💊</span>
                                        <div class="text-zinc-700 dark:text-zinc-300">
                                            <span class="font-medium">{{ $item->medication_name }}</span>
                                            <span class="text-zinc-500 dark:text-zinc-400">
                                                — {{ $item->dose }} · {{ $item->frequency }} · {{ $item->duration }}
                                            </span>
                                            @if ($item->instructions)
                                                <span class="text-zinc-400 dark:text-zinc-500">
                                                    ({{ $item->instructions }})
                                                </span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-zinc-400 dark:text-zinc-500 text-sm italic">Sin recetas registradas.</p>
            @endif
        </section>

        <hr class="border-zinc-200 dark:border-zinc-800" />

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- SECCIÓN 6: HISTORIAL DE LABORATORIOS --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <section id="historial-laboratorios" dusk="section-historial-laboratorios" class="scroll-mt-16">
            <h2 class="text-xl font-bold text-zinc-800 dark:text-zinc-100 mb-4">Historial de Laboratorios</h2>

            @php
                $consultasConLab = $patient->consultations->filter(fn ($c) => $c->laboratoryRequest && $c->laboratoryRequest->items->isNotEmpty());
            @endphp

            @if ($consultasConLab->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($consultasConLab as $consultation)
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex gap-3 items-center text-sm text-zinc-500 dark:text-zinc-400">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $consultation->consultation_date->format('d/m/Y') }}
                                    </span>
                                    <span>{{ $consultation->doctor?->full_name ?? '—' }}</span>
                                </div>
                                <a
                                    href="{{ route('consultas.show', $consultation->id) }}"
                                    class="text-xs text-teal-600 dark:text-teal-400 hover:underline"
                                >
                                    Ver consulta →
                                </a>
                            </div>
                            <ul class="space-y-1.5 text-sm">
                                @foreach ($consultation->laboratoryRequest->items as $item)
                                    <li class="flex gap-3 items-start">
                                        <span class="text-blue-400 flex-shrink-0">🧪</span>
                                        <div class="text-zinc-700 dark:text-zinc-300">
                                            <span class="font-medium">{{ $item->exam_name }}</span>
                                            @if ($item->indications)
                                                <span class="text-zinc-500 dark:text-zinc-400">
                                                    — {{ $item->indications }}
                                                </span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-zinc-400 dark:text-zinc-500 text-sm italic">
                    Sin solicitudes de laboratorio registradas.
                </p>
            @endif
        </section>
    </div>
</x-layouts::app>
