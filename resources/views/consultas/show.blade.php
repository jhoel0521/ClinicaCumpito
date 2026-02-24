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
    </div>
</x-layouts::app>
