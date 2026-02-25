<x-layouts::app :title="__('Detalle de Consulta')">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-blue-700 dark:text-blue-400">Detalle de Consulta</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Vista general del registro clínico</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button :href="route('consultas.edit', $consultation->id)" variant="secondary">
                    Editar
                </x-ui.button>
                <x-ui.button :href="route('consultas.index')" variant="ghost">Volver</x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert type="success" class="mb-4">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-4">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Paciente</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $consultation->patient->full_name }}
                </p>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-4">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Doctor</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $consultation->doctor->full_name }}
                </p>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-4">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Tipo</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ is_object($consultation->type) ? $consultation->type->value() : $consultation->type }}
                </p>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-4">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Estado</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ is_object($consultation->status) ? $consultation->status->value() : $consultation->status }}
                </p>
            </div>

            <div
                class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-4 md:col-span-2"
            >
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Fecha y hora</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ optional($consultation->consultation_date)->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-6">
                <h2 class="text-xl font-semibold text-blue-700 dark:text-blue-400 mb-4">Signos Vitales</h2>

                <form
                    action="{{ $consultation->vitalSigns ? route('consultas.vital-signs.update', $consultation->id) : route('consultas.vital-signs.store', $consultation->id) }}"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf
                    @if ($consultation->vitalSigns)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Peso (kg)
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                name="weight"
                                value="{{ old('weight', $consultation->vitalSigns?->weight?->value()) }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Talla (cm)
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                name="height"
                                value="{{ old('height', $consultation->vitalSigns?->height?->value()) }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Perímetro cefálico (cm)
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                name="head_circumference"
                                value="{{ old('head_circumference', $consultation->vitalSigns?->head_circumference?->value()) }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Temperatura (°C)
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                name="temperature"
                                value="{{ old('temperature', $consultation->vitalSigns?->temperature?->value()) }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <x-ui.button type="submit" variant="primary">
                            {{ $consultation->vitalSigns ? 'Actualizar Signos' : 'Guardar Signos' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-6">
                <h2 class="text-xl font-semibold text-blue-700 dark:text-blue-400 mb-4">Nota SOAP</h2>

                <form
                    action="{{ $consultation->soapNote ? route('consultas.soap-notes.update', $consultation->id) : route('consultas.soap-notes.store', $consultation->id) }}"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf
                    @if ($consultation->soapNote)
                        @method('PUT')
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subjetivo</label>
                        <textarea
                            name="subjective"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                        >
{{ old('subjective', $consultation->soapNote?->subjective) }}</textarea
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Objetivo</label>
                        <textarea
                            name="objective"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                        >
{{ old('objective', $consultation->soapNote?->objective) }}</textarea
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Análisis</label>
                        <textarea
                            name="assessment"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                        >
{{ old('assessment', $consultation->soapNote?->assessment) }}</textarea
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan</label>
                        <textarea
                            name="plan"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                        >
{{ old('plan', $consultation->soapNote?->plan) }}</textarea
                        >
                    </div>

                    <div class="flex justify-end gap-2">
                        <x-ui.button type="submit" variant="primary">
                            {{ $consultation->soapNote ? 'Actualizar SOAP' : 'Guardar SOAP' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-6 mt-6">
            <h2 class="text-xl font-semibold text-blue-700 dark:text-blue-400 mb-4">Receta</h2>

            <form
                action="{{ $consultation->prescription ? route('consultas.prescriptions.update', $consultation->id) : route('consultas.prescriptions.store', $consultation->id) }}"
                method="POST"
                class="space-y-4"
            >
                @csrf
                @if ($consultation->prescription)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Plantilla origen (opcional)
                    </label>
                    <select
                        name="source_template_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        <option value="">Sin plantilla</option>
                        @foreach ($prescriptionTemplates as $template)
                            <option
                                value="{{ $template->id }}"
                                @selected(old('source_template_id', $consultation->prescription?->source_template_id) === $template->id)
                            >
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                    <textarea
                        name="observations"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
{{ old('observations', $consultation->prescription?->observations) }}</textarea
                    >
                </div>

                <div class="flex justify-end gap-2">
                    <x-ui.button type="submit" variant="primary">
                        {{ $consultation->prescription ? 'Actualizar Receta' : 'Guardar Receta' }}
                    </x-ui.button>
                </div>
            </form>

            @if ($consultation->prescription)
                <form
                    action="{{ route('consultas.prescriptions.destroy', $consultation->id) }}"
                    method="POST"
                    class="mt-3 flex justify-end"
                >
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="ghost">Eliminar Receta</x-ui.button>
                </form>

                <div class="mt-6 border-t border-gray-200 dark:border-zinc-800 pt-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        Detalles de receta (snapshot)
                    </h3>

                    <form
                        action="{{ route('consultas.prescription-items.store', $consultation->id) }}"
                        method="POST"
                        class="space-y-3"
                    >
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Medicamento
                                </label>
                                <input
                                    type="text"
                                    name="medication_name"
                                    value="{{ old('medication_name') }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Dosis
                                </label>
                                <input
                                    type="text"
                                    name="dose"
                                    value="{{ old('dose') }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Frecuencia
                                </label>
                                <input
                                    type="text"
                                    name="frequency"
                                    value="{{ old('frequency') }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Duración
                                </label>
                                <input
                                    type="text"
                                    name="duration"
                                    value="{{ old('duration') }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Instrucciones
                            </label>
                            <textarea
                                name="instructions"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                            >
{{ old('instructions') }}</textarea
                            >
                        </div>

                        <div class="flex justify-end">
                            <x-ui.button type="submit" variant="secondary">Agregar Detalle</x-ui.button>
                        </div>
                    </form>

                    <div class="mt-4 space-y-3">
                        @forelse ($consultation->prescription->items as $item)
                            <div class="border border-gray-200 dark:border-zinc-800 rounded-lg p-4">
                                <form
                                    action="{{ route('consultas.prescription-items.update', [$consultation->id, $item->id]) }}"
                                    method="POST"
                                    class="space-y-3"
                                >
                                    @csrf
                                    @method('PUT')

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                        <input
                                            type="text"
                                            name="medication_name"
                                            value="{{ $item->medication_name }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                            required
                                        />
                                        <input
                                            type="text"
                                            name="dose"
                                            value="{{ $item->dose }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                            required
                                        />
                                        <input
                                            type="text"
                                            name="frequency"
                                            value="{{ $item->frequency }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                            required
                                        />
                                        <input
                                            type="text"
                                            name="duration"
                                            value="{{ $item->duration }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <textarea
                                            name="instructions"
                                            rows="2"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                        >
{{ $item->instructions }}</textarea
                                        >
                                    </div>

                                    <div class="flex justify-end">
                                        <x-ui.button type="submit" variant="secondary">Actualizar Detalle</x-ui.button>
                                    </div>
                                </form>

                                <form
                                    action="{{ route('consultas.prescription-items.destroy', [$consultation->id, $item->id]) }}"
                                    method="POST"
                                    class="mt-2 flex justify-end"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="ghost">Eliminar</x-ui.button>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Sin detalles de receta registrados.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-6 mt-6">
            <h2 class="text-xl font-semibold text-blue-700 dark:text-blue-400 mb-4">Solicitud de Laboratorio</h2>

            <form
                action="{{ $consultation->laboratoryRequest ? route('consultas.laboratory-requests.update', $consultation->id) : route('consultas.laboratory-requests.store', $consultation->id) }}"
                method="POST"
                class="space-y-4"
            >
                @csrf
                @if ($consultation->laboratoryRequest)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Plantilla origen (opcional)
                    </label>
                    <select
                        name="source_template_id"
                        dusk="lab-source-template"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        <option value="">Sin plantilla</option>
                        @foreach ($laboratoryTemplates as $template)
                            <option
                                value="{{ $template->id }}"
                                @selected(old('source_template_id', $consultation->laboratoryRequest?->source_template_id) === $template->id)
                            >
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                    <textarea
                        name="observations"
                        dusk="lab-observations"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
{{ old('observations', $consultation->laboratoryRequest?->observations) }}</textarea
                    >
                </div>

                <div class="flex justify-end gap-2">
                    <x-ui.button type="submit" variant="primary">
                        {{ $consultation->laboratoryRequest ? 'Actualizar Solicitud' : 'Guardar Solicitud' }}
                    </x-ui.button>
                </div>
            </form>

            @if ($consultation->laboratoryRequest)
                <form
                    action="{{ route('consultas.laboratory-requests.destroy', $consultation->id) }}"
                    method="POST"
                    class="mt-3 flex justify-end"
                >
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="ghost">Eliminar Solicitud</x-ui.button>
                </form>

                <div class="mt-6 border-t border-gray-200 dark:border-zinc-800 pt-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        Exámenes solicitados (snapshot)
                    </h3>

                    <form
                        action="{{ route('consultas.laboratory-request-items.store', $consultation->id) }}"
                        method="POST"
                        class="space-y-3"
                    >
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nombre del examen
                                </label>
                                <input
                                    type="text"
                                    name="exam_name"
                                    dusk="lab-exam-name"
                                    value="{{ old('exam_name') }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Indicaciones
                                </label>
                                <input
                                    type="text"
                                    name="indications"
                                    dusk="lab-indications"
                                    value="{{ old('indications') }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <x-ui.button type="submit" variant="secondary">Agregar Examen</x-ui.button>
                        </div>
                    </form>

                    <div class="mt-4 space-y-3">
                        @forelse ($consultation->laboratoryRequest->items as $item)
                            <div class="border border-gray-200 dark:border-zinc-800 rounded-lg p-4">
                                <form
                                    action="{{ route('consultas.laboratory-request-items.update', [$consultation->id, $item->id]) }}"
                                    method="POST"
                                    class="space-y-3"
                                >
                                    @csrf
                                    @method('PUT')

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <input
                                            type="text"
                                            name="exam_name"
                                            value="{{ $item->exam_name }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                            required
                                        />
                                        <input
                                            type="text"
                                            name="indications"
                                            value="{{ $item->indications }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                        />
                                    </div>

                                    <div class="flex justify-end">
                                        <x-ui.button type="submit" variant="secondary">Actualizar Examen</x-ui.button>
                                    </div>
                                </form>

                                <form
                                    action="{{ route('consultas.laboratory-request-items.destroy', [$consultation->id, $item->id]) }}"
                                    method="POST"
                                    class="mt-2 flex justify-end"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="ghost">Eliminar</x-ui.button>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Sin exámenes registrados.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-6 mt-6">
            <h2 class="text-xl font-semibold text-blue-700 dark:text-blue-400 mb-4">Vacunas Aplicadas</h2>

            <form
                action="{{ route('consultas.patient-vaccines.store', $consultation->id) }}"
                method="POST"
                class="space-y-4"
            >
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vacuna</label>
                        <select
                            name="vaccine_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                            required
                        >
                            <option value="">Selecciona una vacuna</option>
                            @foreach ($vaccines as $vaccine)
                                <option value="{{ $vaccine->id }}" @selected(old('vaccine_id') === $vaccine->id)>
                                    {{ $vaccine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fecha de aplicación
                        </label>
                        <input
                            type="datetime-local"
                            name="applied_at"
                            value="{{ old('applied_at', now()->format('Y-m-d\\TH:i')) }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dosis #</label>
                        <input
                            type="number"
                            name="dose_number"
                            min="1"
                            max="20"
                            value="{{ old('dose_number') }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                    <textarea
                        name="notes"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
{{ old('notes') }}</textarea
                    >
                </div>

                <div class="flex justify-end">
                    <x-ui.button type="submit" variant="primary">Guardar Vacuna</x-ui.button>
                </div>
            </form>

            <div class="mt-6 space-y-3">
                @forelse ($consultation->patientVaccines as $patientVaccine)
                    <div class="border border-gray-200 dark:border-zinc-800 rounded-lg p-4">
                        <form
                            action="{{ route('consultas.patient-vaccines.update', [$consultation->id, $patientVaccine->id]) }}"
                            method="POST"
                            class="space-y-3"
                        >
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="lg:col-span-2">
                                    <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">
                                        Vacuna
                                    </label>
                                    <select
                                        name="vaccine_id"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                        required
                                    >
                                        @foreach ($vaccines as $vaccine)
                                            <option
                                                value="{{ $vaccine->id }}"
                                                @selected($patientVaccine->vaccine_id === $vaccine->id)
                                            >
                                                {{ $vaccine->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">
                                        Aplicada
                                    </label>
                                    <input
                                        type="datetime-local"
                                        name="applied_at"
                                        value="{{ optional($patientVaccine->applied_at)->format('Y-m-d\\TH:i') }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">
                                        Dosis
                                    </label>
                                    <input
                                        type="number"
                                        name="dose_number"
                                        min="1"
                                        max="20"
                                        value="{{ $patientVaccine->dose_number }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">
                                    Notas
                                </label>
                                <textarea
                                    name="notes"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                >
{{ $patientVaccine->notes }}</textarea
                                >
                            </div>

                            <div class="flex justify-end">
                                <x-ui.button type="submit" variant="secondary">Actualizar Vacuna</x-ui.button>
                            </div>
                        </form>

                        <form
                            action="{{ route('consultas.patient-vaccines.destroy', [$consultation->id, $patientVaccine->id]) }}"
                            method="POST"
                            class="mt-2 flex justify-end"
                        >
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="ghost">Eliminar</x-ui.button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Sin vacunas aplicadas registradas para esta consulta.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts::app>
