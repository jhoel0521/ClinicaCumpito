<?php

use App\Contracts\PrescriptionTemplateServiceContract;
use App\DTOs\Templates\PrescriptionTemplateDTO;
use App\Models\PrescriptionTemplate;
use Livewire\Component;
use Illuminate\Support\Collection;

new class extends Component {
    public string $name = '';
    public string $description = '';
    public bool $isActive = true;
    public array $items = []; // [{custom_medication_name, dose, frequency, duration, instructions}]

    public ?string $editingTemplateId = null;
    public string $search = '';

    public function with(): array
    {
        $doctor = auth()->user()->doctor;

        return [
            'templates' => $doctor
                ? $doctor
                    ->prescriptionTemplates()
                    ->with('items')
                    ->latest()
                    ->get()
                : collect(),
        ];
    }

    public function addItem(): void
    {
        $this->items[] = [
            'custom_medication_name' => '',
            'dose' => '',
            'frequency' => '',
            'duration' => '',
            'instructions' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'template-modal');
    }

    public function save(PrescriptionTemplateServiceContract $service): void
    {
        if ($this->editingTemplateId) {
            $this->authorize('update', PrescriptionTemplate::findOrFail($this->editingTemplateId));
        } else {
            $this->authorize('create', PrescriptionTemplate::class);
        }

        $doctor = auth()->user()->doctor;

        if (! $doctor) {
            $this->dispatch('notify', variant: 'danger', content: __('No tienes un perfil de doctor configurado.'));
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.custom_medication_name' => 'required|string|max:255',
        ]);

        $dto = PrescriptionTemplateDTO::fromArray([
            'id' => $this->editingTemplateId,
            'doctor_id' => $doctor->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'items' => $this->items,
        ]);

        if ($this->editingTemplateId) {
            $service->updatePrescriptionTemplate($this->editingTemplateId, $dto);
            $this->dispatch('notify', variant: 'success', content: __('Plantilla actualizada exitosamente.'));
        } else {
            $service->createPrescriptionTemplate($dto);
            $this->dispatch('notify', variant: 'success', content: __('Plantilla creada exitosamente.'));
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'template-modal');
    }

    public function edit(string $id): void
    {
        $template = PrescriptionTemplate::with('items')->findOrFail($id);
        $this->editingTemplateId = $template->id;
        $this->name = $template->name;
        $this->description = $template->description ?? '';
        $this->isActive = $template->is_active;

        $this->items = $template->items
            ->map(
                fn ($item) => [
                    'id' => $item->id,
                    'custom_medication_name' => $item->custom_medication_name ?? '',
                    'dose' => $item->dose ?? '',
                    'frequency' => $item->frequency ?? '',
                    'duration' => $item->duration ?? '',
                    'instructions' => $item->instructions ?? '',
                ],
            )
            ->toArray();

        $this->dispatch('modal-show', name: 'template-modal');
    }

    public function delete(string $id, PrescriptionTemplateServiceContract $service): void
    {
        $this->authorize('delete', PrescriptionTemplate::findOrFail($id));
        $service->deletePrescriptionTemplate($id);
        $this->dispatch('notify', variant: 'success', content: __('Plantilla eliminada.'));
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'isActive', 'items', 'editingTemplateId']);
    }
}; ?>

<section class="p-6">
    <div class="mb-6 flex justify-between items-end">
        <div>
            <flux:heading size="xl">{{ __('Plantillas de Receta') }}</flux:heading>
            <flux:subheading>
                {{ __('Configura conjuntos de medicamentos para tus diagnósticos más frecuentes.') }}
            </flux:subheading>
        </div>
        <flux:button dusk="btn-nueva-plantilla" variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Nueva Plantilla') }}
        </flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @if ($templates->isEmpty())
            <div
                class="col-span-full py-12 text-center bg-gray-50 dark:bg-zinc-800/50 rounded-xl border-2 border-dashed border-gray-200 dark:border-zinc-700"
            >
                <flux:icon name="clipboard-document-list" class="w-12 h-12 mx-auto text-gray-300 mb-4" />
                <flux:heading>{{ __('No tienes plantillas creadas') }}</flux:heading>
                <flux:text>{{ __('Comienza creando una nueva para agilizar tus consultas.') }}</flux:text>
            </div>
        @else
            @foreach ($templates as $template)
                <flux:card
                    class="flex flex-col justify-between p-6 h-full border-l-4 {{ $template->is_active ? 'border-l-blue-500' : 'border-l-gray-300' }}"
                >
                    <div class="mb-4">
                        <div class="flex justify-between items-start mb-2">
                            <flux:heading size="lg">{{ $template->name }}</flux:heading>
                            <flux:badge :variant="$template->is_active ? 'success' : 'neutral'" size="sm">
                                {{ $template->is_active ? __('Activa') : __('Inactiva') }}
                            </flux:badge>
                        </div>
                        @if ($template->description)
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                                {{ $template->description }}
                            </flux:text>
                        @endif

                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach ($template->items->take(3) as $item)
                                <flux:badge size="sm" variant="outline" class="text-xs">
                                    {{ $item->custom_medication_name }}
                                </flux:badge>
                            @endforeach

                            @if ($template->items->count() > 3)
                                <flux:badge size="sm" variant="outline" class="text-xs">
                                    +{{ $template->items->count() - 3 }}
                                </flux:badge>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-zinc-800">
                        <flux:button variant="ghost" size="sm" icon="pencil" wire:click="edit('{{ $template->id }}')">
                            {{ __('Editar') }}
                        </flux:button>
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="trash"
                            wire:click="delete('{{ $template->id }}')"
                            wire:confirm="{{ __('¿Estás seguro de eliminar esta plantilla?') }}"
                        >
                            {{ __('Eliminar') }}
                        </flux:button>
                    </div>
                </flux:card>
            @endforeach
        @endif
    </div>

    <!-- Modal de Formulario -->
    <flux:modal name="template-modal" class="md:w-[800px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingTemplateId ? __('Editar Plantilla') : __('Nueva Plantilla') }}
                </flux:heading>
                <flux:subheading>{{ __('Define el nombre y los medicamentos de la plantilla.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input
                        wire:model="name"
                        :label="__('Nombre de la Plantilla')"
                        placeholder="Ej: Faringitis Común"
                        required
                    />
                    <div class="flex items-center gap-2 pt-8">
                        <flux:checkbox wire:model="isActive" :label="__('Plantilla Activa')" />
                    </div>
                </div>

                <flux:textarea
                    wire:model="description"
                    :label="__('Descripción (opcional)')"
                    placeholder="Breve nota sobre cuándo aplicar esta plantilla..."
                />

                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b pb-2 dark:border-zinc-800">
                        <flux:heading size="md">{{ __('Medicamentos') }}</flux:heading>
                        <flux:button dusk="btn-agregar-item" size="sm" icon="plus" wire:click="addItem">
                            {{ __('Agregar Item') }}
                        </flux:button>
                    </div>

                    @if (empty($items))
                        <div class="p-4 text-center text-gray-500 bg-gray-50 dark:bg-zinc-800 rounded">
                            {{ __('Agrega al menos un medicamento a la plantilla.') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        @foreach ($items as $index => $item)
                            <div
                                wire:key="item-{{ $index }}"
                                class="p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-lg relative group"
                            >
                                <flux:button
                                    variant="ghost"
                                    size="xs"
                                    icon="x-mark"
                                    class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                                    wire:click="removeItem({{ $index }})"
                                />

                                <div class="mb-4">
                                    <flux:input
                                        wire:model="items.{{ $index }}.custom_medication_name"
                                        :label="__('Nombre del Medicamento')"
                                        :placeholder="__('Ej: Paracetamol 500mg')"
                                        required
                                    />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                                    <flux:input
                                        dusk="input-dosis-{{ $index }}"
                                        wire:model="items.{{ $index }}.dose"
                                        :label="__('Dosis')"
                                        placeholder="Ej: 500mg"
                                    />
                                    <flux:input
                                        dusk="input-frecuencia-{{ $index }}"
                                        wire:model="items.{{ $index }}.frequency"
                                        :label="__('Frecuencia')"
                                        placeholder="cada 8h"
                                    />
                                    <flux:input
                                        dusk="input-duracion-{{ $index }}"
                                        wire:model="items.{{ $index }}.duration"
                                        :label="__('Duración')"
                                        placeholder="7 días"
                                    />
                                </div>

                                <flux:textarea
                                    wire:model="items.{{ $index }}.instructions"
                                    :label="__('Instrucciones')"
                                    rows="1"
                                    placeholder="Ej: Tomar con abundantes líquidos..."
                                />
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($errors->any())
                    <div class="p-3 bg-red-50 text-red-600 rounded text-sm">
                        {{ __('Por favor, completa los campos requeridos en los items.') }}
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>
                    <flux:button dusk="btn-guardar-plantilla" type="submit" variant="primary">
                        {{ __('Guardar Plantilla') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
