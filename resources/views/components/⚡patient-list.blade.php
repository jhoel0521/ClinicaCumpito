<?php

use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public string $filter = 'all'; // all | incomplete | with_scans

    /**
     * Resetea la página al cambiar búsqueda o filtro
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
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
                ->withCount('consultations')
                ->when($this->search, function ($query) {
                    $query->where('full_name', 'like', '%' . $this->search . '%');
                })
                ->when($this->filter === 'incomplete', function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('date_of_birth')->orWhereNull('gender');
                    });
                })
                ->when($this->filter === 'with_scans', function ($query) {
                    $query->whereHas('consultations', function ($q) {
                        $q->where('type', 'manual')->where('pending_transcription', true);
                    });
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div>
    <!-- Barra de Búsqueda y Filtros -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fas fa-search font-light"></i>
            </span>
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                name="search"
                type="text"
                placeholder="Buscar por nombre..."
                class="pl-10 w-full"
                data-ui="search-patients"
            />
        </div>
        <div class="flex gap-2">
            <button
                wire:click="$set('filter', 'all')"
                @class([
                    'px-3 py-1.5 text-xs font-medium rounded-lg transition',
                    'bg-teal-600 text-white' => $filter === 'all',
                    'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' =>
                        $filter !== 'all',
                ])
            >
                Todos
            </button>
            <button
                wire:click="$set('filter', 'incomplete')"
                @class([
                    'px-3 py-1.5 text-xs font-medium rounded-lg transition',
                    'bg-amber-500 text-white' => $filter === 'incomplete',
                    'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' =>
                        $filter !== 'incomplete',
                ])
            >
                Datos incompletos
            </button>
            <button
                wire:click="$set('filter', 'with_scans')"
                @class([
                    'px-3 py-1.5 text-xs font-medium rounded-lg transition',
                    'bg-purple-600 text-white' => $filter === 'with_scans',
                    'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' =>
                        $filter !== 'with_scans',
                ])
            >
                Pendientes digitalizar
            </button>
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
                        Paciente
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
                        class="px-6 py-3 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        Consultas
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        Estado
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
                            <a
                                href="{{ route('pacientes.show', $patient->id) }}"
                                class="font-medium text-gray-900 dark:text-gray-100 hover:text-teal-600 dark:hover:text-teal-400 transition"
                            >
                                {{ $patient->full_name }}
                            </a>
                            @if ($patient->blood_group)
                                <span
                                    class="ml-1.5 inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300"
                                >
                                    {{ $patient->blood_group->value() }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                            @if ($patient->date_of_birth)
                                <div>{{ $patient->date_of_birth->format('d/m/Y') }}</div>
                                <div class="text-xs text-zinc-400">{{ $patient->age()?->forDisplay() }}</div>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($patient->gender)
                                <span
                                    @class([
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-200' => $patient->gender->value() === 'M',
                                        'bg-pink-100 dark:bg-pink-900/50 text-pink-700 dark:text-pink-200' => $patient->gender->value() === 'F',
                                    ])
                                >
                                    {{ $patient->gender->value() === 'M' ? 'Masculino' : 'Femenino' }}
                                </span>
                            @else
                                <span class="text-zinc-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                @class([
                                    'inline-block min-w-[1.5rem] px-1.5 py-0.5 text-xs font-semibold rounded-full',
                                    'bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300' => $patient->consultations_count > 0,
                                    'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' => $patient->consultations_count === 0,
                                ])
                            >
                                {{ $patient->consultations_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if (! $patient->hasCompleteBasicData())
                                <a
                                    href="{{ route('pacientes.edit', $patient->id) }}?require_complete=1"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 hover:bg-amber-200 dark:hover:bg-amber-900/60 transition"
                                    title="Completar datos básicos"
                                >
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                    Incompleto
                                </a>
                            @else
                                <span
                                    class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300"
                                >
                                    Completo
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a
                                    href="{{ route('pacientes.show', $patient->id) }}"
                                    class="text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 transition"
                                    title="Ver Detalles"
                                >
                                    <i class="fas fa-eye text-lg"></i>
                                </a>
                                <a
                                    href="{{ route('pacientes.edit', $patient->id) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition"
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
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
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
