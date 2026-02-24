<x-layouts::app :title="__('Consultas')">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-blue-700 dark:text-blue-400">Consultas</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Gestión del flujo clínico de consultas</p>
            </div>
            <x-ui.button :href="route('consultas.create')" variant="primary">Nueva Consulta</x-ui.button>
        </div>

        @if (session('success'))
            <x-ui.alert type="success" class="mb-4">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 dark:bg-blue-900/20 text-left text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">Paciente</th>
                        <th class="px-4 py-3">Doctor</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                    @forelse ($consultations as $consultation)
                        <tr class="text-gray-800 dark:text-gray-200">
                            <td class="px-4 py-3">{{ $consultation->patient->full_name }}</td>
                            <td class="px-4 py-3">{{ $consultation->doctor->full_name }}</td>
                            <td class="px-4 py-3">
                                {{ is_object($consultation->type) ? $consultation->type->value() : $consultation->type }}
                            </td>
                            <td class="px-4 py-3">
                                {{ is_object($consultation->status) ? $consultation->status->value() : $consultation->status }}
                            </td>
                            <td class="px-4 py-3">
                                {{ optional($consultation->consultation_date)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button
                                        :href="route('consultas.show', $consultation->id)"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        Ver
                                    </x-ui.button>
                                    <x-ui.button
                                        :href="route('consultas.edit', $consultation->id)"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        Editar
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No hay consultas registradas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $consultations->links() }}
        </div>
    </div>
</x-layouts::app>
