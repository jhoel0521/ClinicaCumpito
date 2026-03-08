<x-layouts::app :title="__('Editar Consulta')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-blue-700 dark:text-blue-400">Editar Consulta</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Modificar datos de la consulta</p>
            </div>
            <x-ui.button :href="route('consultas.show', $consultation->id)" variant="secondary">Cancelar</x-ui.button>
        </div>

        @if ($errors->has('status'))
            <x-ui.alert type="error" class="mb-4">
                {{ $errors->first('status') }}
            </x-ui.alert>
        @endif

        <form
            id="consultation-update-form"
            action="{{ route('consultas.update', $consultation->id) }}"
            method="POST"
            class="space-y-6"
        >
            @csrf
            @method('PUT')

            <div
                class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 grid grid-cols-1 md:grid-cols-2 gap-6"
            >
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Paciente</label>
                    <select
                        name="patient_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        @foreach ($patients as $patient)
                            <option
                                value="{{ $patient->id }}"
                                @selected(old('patient_id', $consultation->patient_id) === $patient->id)
                            >
                                {{ $patient->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Doctor</label>
                    <select
                        name="doctor_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        @foreach ($doctors as $doctor)
                            <option
                                value="{{ $doctor->id }}"
                                @selected(old('doctor_id', $consultation->doctor_id) === $doctor->id)
                            >
                                {{ $doctor->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                    <select
                        name="type"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        <option
                            value="digital"
                            @selected(old('type', is_object($consultation->type) ? $consultation->type->value() : $consultation->type) === 'digital')
                        >
                            Digital
                        </option>
                        <option
                            value="manual"
                            @selected(old('type', is_object($consultation->type) ? $consultation->type->value() : $consultation->type) === 'manual')
                        >
                            Manual
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                    <select
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        <option
                            value="draft"
                            @selected(old('status', is_object($consultation->status) ? $consultation->status->value() : $consultation->status) === 'draft')
                        >
                            Draft
                        </option>
                        <option
                            value="saved"
                            @selected(old('status', is_object($consultation->status) ? $consultation->status->value() : $consultation->status) === 'saved')
                        >
                            Saved
                        </option>
                        <option
                            value="finalized"
                            @selected(old('status', is_object($consultation->status) ? $consultation->status->value() : $consultation->status) === 'finalized')
                        >
                            Finalized
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y hora</label>
                    <input
                        type="datetime-local"
                        name="consultation_date"
                        value="{{ old('consultation_date', optional($consultation->consultation_date)->format('Y-m-d\TH:i')) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Archivo escaneado (ruta)
                    </label>
                    <input
                        type="text"
                        name="scanned_file_path"
                        value="{{ old('scanned_file_path', $consultation->scanned_file_path) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input
                            type="checkbox"
                            name="pending_transcription"
                            value="1"
                            @checked(old('pending_transcription', $consultation->pending_transcription))
                            class="rounded border-gray-300 dark:border-zinc-700"
                        />
                        Pendiente de transcripción
                    </label>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <x-ui.button type="submit" form="consultation-delete-form" variant="danger">Eliminar</x-ui.button>

                <x-ui.button type="submit" variant="primary">Actualizar Consulta</x-ui.button>
            </div>
        </form>

        <form
            id="consultation-delete-form"
            action="{{ route('consultas.destroy', $consultation->id) }}"
            method="POST"
            class="hidden"
        >
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-layouts::app>
