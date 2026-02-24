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
    </div>
</x-layouts::app>
