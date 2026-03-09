<x-layouts::app :title="__('Crear Consulta')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-blue-700 dark:text-blue-400">Nueva Consulta</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Registrar una nueva consulta clínica</p>
            </div>
            <x-ui.button :href="route('consultas.index')" variant="secondary">Volver</x-ui.button>
        </div>

        @if ($errors->any())
            <x-ui.alert type="error" class="mb-4">Verifica los campos obligatorios antes de continuar.</x-ui.alert>
        @endif

        <form action="{{ route('consultas.store') }}" method="POST" class="space-y-6">
            @csrf

            <div
                class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 grid grid-cols-1 md:grid-cols-2 gap-6"
            >
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Paciente</label>
                    <select
                        name="patient_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        <option value="">-- Seleccione --</option>
                        @foreach ($patients as $p)
                            <option value="{{ $p->id }}" @selected(old('patient_id', $patient?->id) === $p->id)>
                                {{ $p->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Doctor</label>
                    <select
                        name="doctor_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        <option value="">-- Seleccione --</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected(old('doctor_id') === $doctor->id)>
                                {{ $doctor->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                    <select
                        name="type"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        <option value="digital" @selected(old('type', 'digital') === 'digital')>Digital</option>
                        <option value="manual" @selected(old('type') === 'manual')>Manual</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                    <select
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                        <option value="saved" @selected(old('status', 'saved') === 'saved')>Saved</option>
                        <option value="finalized" @selected(old('status') === 'finalized')>Finalized</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y hora</label>
                    <input
                        type="datetime-local"
                        name="consultation_date"
                        value="{{ old('consultation_date', now()->format('Y-m-d\TH:i')) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    />
                    @error('consultation_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Archivo escaneado (ruta)
                    </label>
                    <input
                        type="text"
                        name="scanned_file_path"
                        value="{{ old('scanned_file_path') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input
                            type="checkbox"
                            name="pending_transcription"
                            value="1"
                            @checked(old('pending_transcription'))
                            class="rounded border-gray-300 dark:border-zinc-700"
                        />
                        Pendiente de transcripción
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <x-ui.button type="submit" variant="primary">Guardar Consulta</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts::app>
