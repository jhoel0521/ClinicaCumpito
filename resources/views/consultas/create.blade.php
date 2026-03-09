<x-layouts::app :title="__('Nueva Consulta')">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-blue-700 dark:text-blue-400">Nueva Consulta</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Selecciona el paciente y confirma la fecha</p>
            </div>
            <x-ui.button :href="route('consultas.index')" variant="secondary">Volver</x-ui.button>
        </div>

        @if ($errors->any())
            <x-ui.alert type="error" class="mb-4">Verifica los campos antes de continuar.</x-ui.alert>
        @endif

        <form action="{{ route('consultas.store') }}" method="POST" class="space-y-6">
            @csrf

            <div
                class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 space-y-6"
            >
                <div>
                    <label for="patient_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Paciente
                    </label>
                    <select
                        id="patient_id"
                        name="patient_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    >
                        <option value="">-- Seleccione --</option>
                        @foreach ($patients as $p)
                            <option value="{{ $p->id }}" @selected(old('patient_id', $patient?->id) == $p->id)>
                                {{ $p->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label
                        for="consultation_date"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                        Fecha y hora
                    </label>
                    <input
                        id="consultation_date"
                        type="datetime-local"
                        name="consultation_date"
                        value="{{ old('consultation_date', now()->format('Y-m-d\TH:i')) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                    />
                    @error('consultation_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <x-ui.button type="submit" variant="primary">Iniciar Consulta</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts::app>
