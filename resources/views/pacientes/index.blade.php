<x-layouts::app :title="__('Pacientes')">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Pacientes</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Gestión de pacientes del sistema</p>
            </div>
            <x-ui.button :href="route('pacientes.create')" variant="primary">
                <i class="fas fa-plus"></i>
                Nuevo Paciente
            </x-ui.button>
        </div>

        <!-- Mensajes Flash -->
        @if (session('success'))
            <x-ui.alert type="success" class="mb-4">
                <p class="flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </p>
            </x-ui.alert>
        @endif

        <!-- Lista de Pacientes -->
        <div
            class="bg-white dark:bg-zinc-900 shadow-md rounded-lg overflow-hidden border border-gray-100 dark:border-zinc-800"
        >
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50 dark:bg-zinc-800">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Nombre
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Fecha Nac.
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Género
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Grupo Sanguíneo
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                    @forelse ($patients as $patient)
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $patient->full_name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $patient->date_of_birth->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full {{ $patient->gender->value() === 'M' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-200' : 'bg-pink-100 dark:bg-pink-900/50 text-pink-700 dark:text-pink-200' }}"
                                >
                                    {{ $patient->gender->value() === 'M' ? 'Masculino' : 'Femenino' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $patient->blood_group?->value() ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <a
                                    href="{{ route('pacientes.show', $patient->id) }}"
                                    class="text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 font-medium transition"
                                >
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a
                                    href="{{ route('pacientes.edit', $patient->id) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition"
                                >
                                    <i class="fas fa-edit"></i>
                                </a>
                                <x-ui.modal
                                    :id="'delete-patient-'.$patient->id"
                                    title="Confirmar eliminación"
                                    triggerText="Eliminar"
                                    class="inline-block"
                                >
                                    <p>
                                        ¿Está seguro de eliminar a
                                        <strong>{{ $patient->full_name }}</strong>
                                        ?
                                    </p>
                                    <div class="mt-4 flex justify-end gap-2">
                                        <form action="{{ route('pacientes.destroy', $patient->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger">Eliminar</x-ui.button>
                                        </form>
                                    </div>
                                </x-ui.modal>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-inbox text-4xl mb-2 opacity-50"></i>
                                <p class="mt-2">No hay pacientes registrados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($patients->hasPages())
            <div class="mt-6">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</x-layouts::app>
