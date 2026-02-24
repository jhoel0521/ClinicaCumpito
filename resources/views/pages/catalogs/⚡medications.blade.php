<?php

use App\DTOs\Catalogs\MedicationDTO;
use App\Contracts\CatalogServiceContract;
use App\Models\Medication;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    // Medication Form
    public string $name = '';
    public string $genericName = '';
    public string $pharmaceuticalForm = '';
    public string $concentration = '';
    public ?string $editingMedicationId = null;

    public function with(): array
    {
        return [
            'medications' => Medication::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('generic_name', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(10),
        ];
    }

    public function save(CatalogServiceContract $service): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'genericName' => 'nullable|string|max:255',
            'pharmaceuticalForm' => 'nullable|string|max:255',
            'concentration' => 'nullable|string|max:255',
        ]);

        $dto = new MedicationDTO(
            id: $this->editingMedicationId,
            name: $this->name,
            generic_name: $this->genericName,
            pharmaceutical_form: $this->pharmaceuticalForm,
            concentration: $this->concentration,
        );

        if ($this->editingMedicationId) {
            $service->updateMedication($this->editingMedicationId, $dto);
        } else {
            $service->createMedication($dto);
        }

        $this->resetForm();
        $this->dispatch('close-modal', 'medication-modal');
    }

    public function edit(string $id): void
    {
        $medication = Medication::findOrFail($id);
        $this->editingMedicationId = $medication->id;
        $this->name = $medication->name;
        $this->genericName = $medication->generic_name ?? '';
        $this->pharmaceuticalForm = $medication->pharmaceutical_form ?? '';
        $this->concentration = $medication->concentration ?? '';
        $this->dispatch('open-modal', 'medication-modal');
    }

    public function delete(string $id, CatalogServiceContract $service): void
    {
        $service->deleteMedication($id);
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'genericName', 'pharmaceuticalForm', 'concentration', 'editingMedicationId']);
    }
}; ?>

<section class="p-6">
    <div class="mb-6 flex justify-between items-end">
        <div>
            <flux:heading size="xl">{{ __('Medicamentos') }}</flux:heading>
            <flux:subheading>{{ __('Gestiona el catálogo de fármacos disponibles para las recetas.') }}
            </flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="$dispatch('open-modal', 'medication-modal')">
            {{ __('Nuevo Medicamento') }}
        </flux:button>
    </div>

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre..."
            icon="magnifying-glass" />
    </div>

    <div
        class="bg-white dark:bg-zinc-900 shadow-md rounded-lg overflow-hidden border border-gray-100 dark:border-zinc-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Nombre Comercial') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Nombre Genérico') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Forma Farmacéutica') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Concentración') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                @foreach ($medications as $medication)
                    <tr wire:key="{{ $medication->id }}">
                        <td class="px-6 py-4">{{ $medication->name }}</td>
                        <td class="px-6 py-4">{{ $medication->generic_name }}</td>
                        <td class="px-6 py-4">{{ $medication->pharmaceutical_form }}</td>
                        <td class="px-6 py-4">{{ $medication->concentration }}</td>
                        <td class="px-6 py-4 flex gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil"
                                wire:click="edit('{{ $medication->id }}')" />
                            <flux:button variant="ghost" size="sm" icon="trash"
                                wire:click="delete('{{ $medication->id }}')" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
            {{ $medications->links() }}
        </div>
    </div>

    <!-- Modal -->
    <flux:modal name="medication-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingMedicationId ? __('Editar Medicamento') : __('Nuevo Medicamento') }}
                </flux:heading>
                <flux:subheading>{{ __('Ingresa los detalles del fármaco.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="name" :label="__('Nombre Comercial')" required />
                <flux:input wire:model="genericName" :label="__('Nombre Genérico')" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="pharmaceuticalForm" :label="__('Forma Farmacéutica')"
                        placeholder="Tab, Jarabe..." />
                    <flux:input wire:model="concentration" :label="__('Concentración')" placeholder="500mg, 10%..." />
                </div>

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