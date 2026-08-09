<?php

use App\Contracts\CatalogServiceContract;
use App\DTOs\Catalogs\MedicalConditionDTO;
use App\Models\MedicalCondition;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public string $name = '';
    public string $description = '';
    public ?string $editingId = null;

    public function with(): array
    {
        return [
            'conditions' => MedicalCondition::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(15),
        ];
    }

    public function save(CatalogServiceContract $service): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $dto = new MedicalConditionDTO(
            id: $this->editingId,
            name: $this->name,
            description: $this->description ?: null,
        );

        if ($this->editingId) {
            $service->updateMedicalCondition($this->editingId, $dto);
        } else {
            $service->createMedicalCondition($dto);
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'condition-modal');
    }

    public function edit(string $id): void
    {
        $condition = MedicalCondition::findOrFail($id);
        $this->editingId = $condition->id;
        $this->name = $condition->name;
        $this->description = $condition->description ?? '';
        $this->dispatch('modal-show', name: 'condition-modal');
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'condition-modal');
    }

    public function delete(string $id, CatalogServiceContract $service): void
    {
        $service->deleteMedicalCondition($id);
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'editingId']);
    }
}; ?>

<section class="p-6">
    <div class="mb-6 flex justify-between items-end">
        <div>
            <flux:heading size="xl">{{ __('Condiciones Médicas') }}</flux:heading>
            <flux:subheading>
                {{ __('Gestiona las condiciones médicas disponibles para el historial de pacientes.') }}
            </flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Nueva Condición') }}
        </flux:button>
    </div>

    <div class="mb-4">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre..."
            icon="magnifying-glass"
        />
    </div>

    <div
        class="bg-white dark:bg-zinc-900 shadow-md rounded-lg overflow-hidden border border-gray-100 dark:border-zinc-800"
    >
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Nombre') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Descripción') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Pacientes') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                @foreach ($conditions as $condition)
                    <tr wire:key="{{ $condition->id }}">
                        <td class="px-6 py-4 font-medium">{{ $condition->name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            {{ $condition->description ?? '—' }}
                        </td>
                        <td class="px-6 py-4">{{ $condition->patients()->count() }}</td>
                        <td class="px-6 py-4 flex gap-2">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="pencil"
                                wire:click="edit('{{ $condition->id }}')"
                            />
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                wire:click="delete('{{ $condition->id }}')"
                                data-swal-confirm="{{ __('¿Eliminar esta condición médica?') }}"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
            {{ $conditions->links() }}
        </div>
    </div>

    <!-- Modal -->
    <flux:modal name="condition-modal" class="md:w-[480px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Editar Condición') : __('Nueva Condición Médica') }}
                </flux:heading>
                <flux:subheading>{{ __('Nombre y descripción de la condición.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model="name"
                    :label="__('Nombre')"
                    required
                    placeholder="Diabetes, Asma, Hipertensión..."
                />
                <flux:textarea
                    wire:model="description"
                    :label="__('Descripción')"
                    rows="3"
                    placeholder="Descripción opcional..."
                />

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">{{ __('Guardar') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
