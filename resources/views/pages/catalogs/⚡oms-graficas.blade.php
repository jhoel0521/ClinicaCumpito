<?php

use App\Contracts\CatalogServiceContract;
use App\DTOs\Catalogs\OmsCatalogoGraficaDTO;
use App\Models\OmsCatalogoGrafica;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    // Form fields
    public string $nombre = '';
    public string $codigo = '';
    public string $descripcion = '';
    public string $tipoGrafica = 'peso_talla';
    public string $rangoEdad = '';
    public string $sexo = 'M';
    public int $minimoZScore = -3;
    public int $maximoZScore = 3;
    public int $minimoPercentil = 3;
    public int $maximoPercentil = 97;
    public ?string $editingId = null;

    public function with(): array
    {
        return [
            'graficas' => OmsCatalogoGrafica::query()
                ->when(
                    $this->search,
                    fn ($q) => $q
                        ->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('codigo', 'like', "%{$this->search}%"),
                )
                ->orderBy('nombre')
                ->paginate(15),
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'oms-graficas-modal');
    }

    public function save(CatalogServiceContract $service): void
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:100',
            'tipoGrafica' => 'required|string|in:peso_talla,talla_edad,peso_edad,perimetro_cefalico,imc',
            'rangoEdad' => 'required|string|max:100',
            'sexo' => 'required|string|in:M,F',
            'descripcion' => 'nullable|string',
            'minimoZScore' => 'required|integer',
            'maximoZScore' => 'required|integer',
            'minimoPercentil' => 'required|integer|min:0|max:100',
            'maximoPercentil' => 'required|integer|min:0|max:100',
        ]);

        $dto = new OmsCatalogoGraficaDTO(
            id: $this->editingId,
            nombre: $this->nombre,
            codigo: $this->codigo,
            descripcion: $this->descripcion ?: null,
            tipo_grafica: $this->tipoGrafica,
            rango_edad: $this->rangoEdad,
            sexo: $this->sexo,
            minimo_z_score: $this->minimoZScore,
            maximo_z_score: $this->maximoZScore,
            minimo_percentil: $this->minimoPercentil,
            maximo_percentil: $this->maximoPercentil,
        );

        if ($this->editingId) {
            $service->updateOmsCatalogo($this->editingId, $dto);
        } else {
            $service->createOmsCatalogo($dto);
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'oms-graficas-modal');
    }

    public function edit(string $id): void
    {
        $grafica = OmsCatalogoGrafica::findOrFail($id);
        $this->editingId = $grafica->id;
        $this->nombre = $grafica->nombre;
        $this->codigo = $grafica->codigo;
        $this->descripcion = $grafica->descripcion ?? '';
        $this->tipoGrafica = $grafica->tipo_grafica;
        $this->rangoEdad = $grafica->rango_edad;
        $this->sexo = $grafica->sexo;
        $this->minimoZScore = $grafica->minimo_z_score;
        $this->maximoZScore = $grafica->maximo_z_score;
        $this->minimoPercentil = $grafica->minimo_percentil;
        $this->maximoPercentil = $grafica->maximo_percentil;
        $this->dispatch('modal-show', name: 'oms-graficas-modal');
    }

    public function delete(string $id, CatalogServiceContract $service): void
    {
        $service->deleteOmsCatalogo($id);
    }

    public function resetForm(): void
    {
        $this->reset([
            'nombre',
            'codigo',
            'descripcion',
            'tipoGrafica',
            'rangoEdad',
            'sexo',
            'minimoZScore',
            'maximoZScore',
            'minimoPercentil',
            'maximoPercentil',
            'editingId',
        ]);
        $this->tipoGrafica = 'peso_talla';
        $this->sexo = 'M';
        $this->minimoZScore = -3;
        $this->maximoZScore = 3;
        $this->minimoPercentil = 3;
        $this->maximoPercentil = 97;
    }
}; ?>

<section class="p-6">
    <div class="mb-6 flex justify-between items-end">
        <div>
            <flux:heading size="xl">{{ __('Gráficas OMS') }}</flux:heading>
            <flux:subheading>
                {{ __('Catálogo de boletas OMS para seguimiento del crecimiento infantil.') }}
            </flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openCreateModal" dusk="btn-nueva-grafica">
            {{ __('Nueva Gráfica') }}
        </flux:button>
    </div>

    <div class="mb-4">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre o código..."
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
                        {{ __('Código') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Tipo') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Rango Edad') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Sexo') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                @foreach ($graficas as $grafica)
                    <tr wire:key="{{ $grafica->id }}">
                        <td class="px-6 py-4">{{ $grafica->nombre }}</td>
                        <td class="px-6 py-4 font-mono text-xs">{{ $grafica->codigo }}</td>
                        <td class="px-6 py-4">{{ str_replace('_', ' ', $grafica->tipo_grafica) }}</td>
                        <td class="px-6 py-4">{{ $grafica->rango_edad }}</td>
                        <td class="px-6 py-4">{{ $grafica->sexo === 'M' ? __('Masculino') : __('Femenino') }}</td>
                        <td class="px-6 py-4 flex gap-2">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="table-cells"
                                :href="route('catalogs.oms-datos', $grafica->id)"
                                dusk="btn-ver-datos-{{ $grafica->id }}"
                            />
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="pencil"
                                wire:click="edit('{{ $grafica->id }}')"
                            />
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                wire:click="delete('{{ $grafica->id }}')"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
            {{ $graficas->links() }}
        </div>
    </div>

    <!-- Modal -->
    <flux:modal name="oms-graficas-modal" class="md:w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Editar Gráfica OMS') : __('Nueva Gráfica OMS') }}
                </flux:heading>
                <flux:subheading>{{ __('Define los metadatos de la boleta OMS.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="nombre" :label="__('Nombre')" required />
                    <flux:input wire:model="codigo" :label="__('Código')" required />
                </div>

                <flux:textarea wire:model="descripcion" :label="__('Descripción')" rows="2" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model="tipoGrafica" :label="__('Tipo de Gráfica')" required>
                        <flux:select.option value="peso_talla">{{ __('Peso para Talla') }}</flux:select.option>
                        <flux:select.option value="talla_edad">{{ __('Talla para Edad') }}</flux:select.option>
                        <flux:select.option value="peso_edad">{{ __('Peso para Edad') }}</flux:select.option>
                        <flux:select.option value="perimetro_cefalico">
                            {{ __('Perímetro Cefálico') }}
                        </flux:select.option>
                        <flux:select.option value="imc">{{ __('IMC') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="sexo" :label="__('Sexo')" required>
                        <flux:select.option value="M">{{ __('Masculino') }}</flux:select.option>
                        <flux:select.option value="F">{{ __('Femenino') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:input wire:model="rangoEdad" :label="__('Rango de Edad')" placeholder="Ej. 0-24 meses" required />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="number" wire:model="minimoZScore" :label="__('Z-Score Mínimo')" required />
                    <flux:input type="number" wire:model="maximoZScore" :label="__('Z-Score Máximo')" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="number" wire:model="minimoPercentil" :label="__('Percentil Mínimo')" required />
                    <flux:input type="number" wire:model="maximoPercentil" :label="__('Percentil Máximo')" required />
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" dusk="btn-guardar-grafica">
                        {{ __('Guardar') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
