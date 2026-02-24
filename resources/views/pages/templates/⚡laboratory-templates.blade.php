<?php

use App\Contracts\TemplateServiceContract;
use App\Contracts\CatalogServiceContract;
use App\DTOs\Templates\LaboratoryTemplateDTO;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryTemplate;
use Livewire\Component;

new class extends Component {
    public string $name = '';
    public string $description = '';
    public bool $isActive = true;
    public array $items = []; // [{laboratory_exam_id, indications}]

    public ?string $editingTemplateId = null;

    public function with(CatalogServiceContract $catalogService): array
    {
        $doctor = auth()->user()->doctor;

        return [
            'templates' => $doctor
                ? $doctor->laboratoryTemplates()->with('items.exam')->latest()->get()
                : collect(),
            'exams' => $catalogService->getAllLaboratoryCategories()->load('exams'),
        ];
    }

    public function addItem(): void
    {
        $this->items[] = [
            'laboratory_exam_id' => '',
            'indications' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(TemplateServiceContract $service): void
    {
        $doctor = auth()->user()->doctor;

        if (!$doctor) {
            $this->dispatch('notify', variant: 'danger', content: __('No tienes un perfil de doctor configurado.'));
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.laboratory_exam_id' => 'required|exists:laboratory_exams,id',
        ]);

        $dto = LaboratoryTemplateDTO::fromArray([
            'id' => $this->editingTemplateId,
            'doctor_id' => $doctor->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'items' => $this->items,
        ]);

        if ($this->editingTemplateId) {
            $service->updateLaboratoryTemplate($this->editingTemplateId, $dto);
            $this->dispatch('notify', variant: 'success', content: __('Plantilla actualizada exitosamente.'));
        } else {
            $service->createLaboratoryTemplate($dto);
            $this->dispatch('notify', variant: 'success', content: __('Plantilla creada exitosamente.'));
        }

        $this->resetForm();
        $this->dispatch('close-modal', 'template-modal');
    }

    public function edit(string $id): void
    {
        $template = LaboratoryTemplate::with('items')->findOrFail($id);
        $this->editingTemplateId = $template->id;
        $this->name = $template->name;
        $this->description = $template->description ?? '';
        $this->isActive = $template->is_active;

        $this->items = $template->items->map(fn($item) => [
            'id' => $item->id,
            'laboratory_exam_id' => $item->laboratory_exam_id,
            'indications' => $item->indications ?? '',
        ])->toArray();

        $this->dispatch('open-modal', 'template-modal');
    }

    public function delete(string $id, TemplateServiceContract $service): void
    {
        $service->deleteLaboratoryTemplate($id);
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
            <flux:heading size="xl">{{ __('Plantillas de Laboratorio') }}</flux:heading>
            <flux:subheading>
                {{ __('Configura conjuntos de exámenes y estudios por especialidad.') }}
            </flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="addItem"
            onclick="$dispatch('open-modal', { name: 'template-modal' })">
            {{ __('Nueva Plantilla') }}
        </flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($templates as $template)
            <flux:card
                class="flex flex-col justify-between p-6 h-full border-l-4 {{ $template->is_active ? 'border-l-purple-500' : 'border-l-gray-300' }}">
                <div class="mb-4">
                    <div class="flex justify-between items-start mb-2">
                        <flux:heading size="lg">{{ $template->name }}</flux:heading>
                        <flux:badge :variant="$template->is_active ? 'success' : 'neutral'" size="sm">
                            {{ $template->is_active ? __('Activa') : __('Inactiva') }}
                        </flux:badge>
                    </div>
                    @if($template->description)
                        <flux:text class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                            {{ $template->description }}
                        </flux:text>
                    @endif
                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach($template->items->take(3) as $item)
                            <flux:badge size="sm" variant="outline" class="text-xs">
                                {{ $item->exam?->name }}
                            </flux:badge>
                        @endforeach
                        @if($template->items->count() > 3)
                            <flux:badge size="sm" variant="outline" class="text-xs">+{{ $template->items->count() - 3 }}
                            </flux:badge>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-zinc-800">
                    <flux:button variant="ghost" size="sm" icon="pencil" wire:click="edit('{{ $template->id }}')">
                        {{ __('Editar') }}
                    </flux:button>
                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="delete('{{ $template->id }}')"
                        wire:confirm="{{ __('¿Estás seguro de eliminar esta plantilla?') }}">
                        {{ __('Eliminar') }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <div
                class="col-span-full py-12 text-center bg-gray-50 dark:bg-zinc-800/50 rounded-xl border-2 border-dashed border-gray-200 dark:border-zinc-700">
                <flux:icon name="beaker" class="w-12 h-12 mx-auto text-gray-300 mb-4" />
                <flux:heading>{{ __('No tienes plantillas creadas') }}</flux:heading>
                <flux:text>{{ __('Comienza creando una nueva para agilizar tus solicitudes.') }}</flux:text>
            </div>
        @endforelse
    </div>

    <!-- Modal de Formulario -->
    <flux:modal name="template-modal" class="md:w-[800px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingTemplateId ? __('Editar Plantilla') : __('Nueva Plantilla') }}
                </flux:heading>
                <flux:subheading>{{ __('Define el nombre y los exámenes de la plantilla.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="name" :label="__('Nombre de la Plantilla')"
                        placeholder="Ej: Perfil Prenatal" required />
                    <div class="flex items-center gap-2 pt-8">
                        <flux:checkbox wire:model="isActive" :label="__('Plantilla Activa')" />
                    </div>
                </div>

                <flux:textarea wire:model="description" :label="__('Descripción (opcional)')"
                    placeholder="Ej: Exámenes requeridos para el primer control..." />

                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b pb-2 dark:border-zinc-800">
                        <flux:heading size="md">{{ __('Exámenes Clínicos') }}</flux:heading>
                        <flux:button size="sm" icon="plus" wire:click="addItem">{{ __('Agregar Examen') }}</flux:button>
                    </div>

                    @if(empty($items))
                        <div class="p-4 text-center text-gray-500 bg-gray-50 dark:bg-zinc-800 rounded">
                            {{ __('Agrega al menos un examen a la plantilla.') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        @foreach($items as $index => $item)
                            <div wire:key="item-{{ $index }}"
                                class="p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-lg relative group">
                                <flux:button variant="ghost" size="xs" icon="x-mark"
                                    class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                                    wire:click="removeItem({{ $index }})" />

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1">
                                        <flux:label>{{ __('Examen del Catálogo') }}</flux:label>
                                        <select wire:model="items.{{ $index }}.laboratory_exam_id"
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-700">
                                            <option value="">{{ __('--- Seleccionar Examen ---') }}</option>
                                            @foreach($exams as $category)
                                                <optgroup label="{{ $category->name }}">
                                                    @foreach($category->exams as $exam)
                                                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                    <flux:input wire:model="items.{{ $index }}.indications"
                                        :label="__('Indicaciones Especiales')"
                                        :placeholder="__('Ej: Ayuno, recolectar primera orina...')" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($errors->any())
                    <div class="p-3 bg-red-50 text-red-600 rounded text-sm">
                        {{ __('Por favor, selecciona un examen para todos los items.') }}
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">{{ __('Guardar Plantilla') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>