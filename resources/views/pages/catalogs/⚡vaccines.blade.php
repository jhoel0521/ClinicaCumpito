<?php

use App\DTOs\Catalogs\VaccineDTO;
use App\Contracts\CatalogServiceContract;
use App\Models\Vaccine;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    // Vaccine Form
    public string $name = '';
    public string $diseasePrevented = '';
    public string $recommendedAge = '';
    public int $doseSequence = 1;
    public ?string $editingVaccineId = null;

    public function with(): array
    {
        return [
            'vaccines' => Vaccine::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('disease_prevented', 'like', "%{$this->search}%"))
                ->orderBy('dose_sequence')
                ->paginate(15),
        ];
    }

    public function save(CatalogServiceContract $service): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'diseasePrevented' => 'nullable|string|max:255',
            'recommendedAge' => 'nullable|string|max:255',
            'doseSequence' => 'required|integer|min:1',
        ]);

        $dto = new VaccineDTO(
            id: $this->editingVaccineId,
            name: $this->name,
            disease_prevented: $this->diseasePrevented,
            recommended_age: $this->recommendedAge,
            dose_sequence: $this->doseSequence,
        );

        if ($this->editingVaccineId) {
            $service->updateVaccine($this->editingVaccineId, $dto);
        } else {
            $service->createVaccine($dto);
        }

        $this->resetForm();
        $this->dispatch('close-modal', 'vaccine-modal');
    }

    public function edit(string $id): void
    {
        $vaccine = Vaccine::findOrFail($id);
        $this->editingVaccineId = $vaccine->id;
        $this->name = $vaccine->name;
        $this->diseasePrevented = $vaccine->disease_prevented ?? '';
        $this->recommendedAge = $vaccine->recommended_age ?? '';
        $this->doseSequence = $vaccine->dose_sequence ?? 1;
        $this->dispatch('open-modal', 'vaccine-modal');
    }

    public function delete(string $id, CatalogServiceContract $service): void
    {
        $service->deleteVaccine($id);
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'diseasePrevented', 'recommendedAge', 'doseSequence', 'editingVaccineId']);
    }
}; ?>

<section class="p-6">
    <div class="mb-6 flex justify-between items-end">
        <div>
            <flux:heading size="xl">{{ __('Vacunas') }}</flux:heading>
            <flux:subheading>{{ __('Gestiona el esquema de vacunación y dosis recomendadas.') }}
            </flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="$dispatch('open-modal', 'vaccine-modal')">
            {{ __('Nueva Vacuna') }}
        </flux:button>
    </div>

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar vacuna..." icon="magnifying-glass" />
    </div>

    <div
        class="bg-white dark:bg-zinc-900 shadow-md rounded-lg overflow-hidden border border-gray-100 dark:border-zinc-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Sec.') }}</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Nombre') }}</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Enfermedad Prevenida') }}</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Edad Rec.') }}</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Acciones') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                @foreach ($vaccines as $vaccine)
                    <tr wire:key="{{ $vaccine->id }}">
                        <td class="px-6 py-4">{{ $vaccine->dose_sequence }}</td>
                        <td class="px-6 py-4">{{ $vaccine->name }}</td>
                        <td class="px-6 py-4">{{ $vaccine->disease_prevented }}</td>
                        <td class="px-6 py-4">{{ $vaccine->recommended_age }}</td>
                        <td class="px-6 py-4 flex gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil" wire:click="edit('{{ $vaccine->id }}')" />
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="delete('{{ $vaccine->id }}')" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
            {{ $vaccines->links() }}
        </div>
    </div>

    <!-- Modal -->
    <flux:modal name="vaccine-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingVaccineId ? __('Editar Vacuna') : __('Nueva Vacuna') }}
                </flux:heading>
                <flux:subheading>{{ __('Ingresa los detalles de la vacuna y su esquema.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="name" :label="__('Nombre de la Vacuna')" required />
                <flux:input wire:model="diseasePrevented" :label="__('Enfermedad Prevenida')" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="recommendedAge" :label="__('Edad Recomendada')" placeholder="Ej. 2 meses" />
                    <flux:input type="number" wire:model="doseSequence" :label="__('Secuencia (Dosis)')" required />
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