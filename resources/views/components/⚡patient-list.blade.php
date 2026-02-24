<?php

use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    /**
     * Actualiza la búsqueda y resetea la página
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Define los datos para la vista
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'patients' => Patient::query()
                ->when($this->search, function ($query) {
                    $query
                        ->where('full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('id_number', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div>
    <!-- Barra de Búsqueda -->
    <div class="mb-6">
        <div class="relative max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fas fa-search font-light"></i>
            </span>
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                name="search"
                type="text"
                placeholder="Buscar por nombre o documento..."
                class="pl-10 w-full"
                data-ui="search-patients"
            />
        </div>
    </div>

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
            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                @forelse ($patients as $patient)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $patient->full_name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                            {{ $patient->date_of_birth->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full {{ $patient->gender->value() === 'M' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-200' : 'bg-pink-100 dark:bg-pink-900/50 text-pink-700 dark:text-pink-200' }}"
                            >
                                {{ $patient->gender->value() === 'M' ? 'Masculino' : 'Femenino' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">
                            {{ $patient->blood_group?->value() ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap space-x-2">
                            <a
                                href="{{ route('pacientes.show', $patient->id) }}"
                                class="text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 font-medium transition"
                                title="Ver Detalles"
                            >
                                <i class="fas fa-eye text-lg"></i>
                            </a>
                            <a
                                href="{{ route('pacientes.edit', $patient->id) }}"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition"
                                title="Editar"
                            >
                                <i class="fas fa-edit text-lg"></i>
                            </a>

                            <x-ui.modal
                                :id="'delete-patient-' . $patient->id"
                                title="Confirmar eliminación"
                                triggerText=""
                                class="inline-block"
                            >
                                <x-slot name="trigger">
                                    <button
                                        class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition"
                                        title="Eliminar"
                                    >
                                        <i class="fas fa-trash-alt text-lg"></i>
                                    </button>
                                </x-slot>
                                <p class="text-gray-700 dark:text-gray-300">
                                    ¿Está seguro de eliminar a
                                    <strong>{{ $patient->full_name }}</strong>
                                    ? Esta acción no se puede deshacer.
                                </p>
                                <div class="mt-6 flex justify-end gap-3">
                                    <form action="{{ route('pacientes.destroy', $patient->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="danger">Eliminar Paciente</x-ui.button>
                                    </form>
                                </div>
                            </x-ui.modal>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-search text-4xl mb-3 opacity-20"></i>
                                <p>No se encontraron pacientes que coincidan con la búsqueda.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $patients->links() }}
    </div>
</div>
