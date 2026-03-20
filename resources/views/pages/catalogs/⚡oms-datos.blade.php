<?php

use App\Contracts\CatalogServiceContract;
use App\DTOs\Catalogs\OmsDatoGraficaDTO;
use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $graficaId = '';
    public string $graficaNombre = '';
    public string $graficaCodigo = '';

    // Form fields — LMS requeridos (string para binding seguro con campos vacíos)
    public string $xValue = '';
    public string $lValue = '';
    public string $mValue = '';
    public string $sValue = '';

    // SD opcionales
    public string $sd3neg = '';
    public string $sd2neg = '';
    public string $sd1neg = '';
    public string $sd0 = '';
    public string $sd1 = '';
    public string $sd2 = '';
    public string $sd3 = '';

    // Percentiles opcionales
    public string $p3 = '';
    public string $p15 = '';
    public string $p50 = '';
    public string $p85 = '';
    public string $p97 = '';

    public ?string $editingId = null;

    public function mount(string $graficaId): void
    {
        $grafica = OmsCatalogoGrafica::findOrFail($graficaId);
        $this->graficaId = $graficaId;
        $this->graficaNombre = $grafica->nombre;
        $this->graficaCodigo = $grafica->codigo;
    }

    public function with(): array
    {
        return [
            'datos' => OmsDatoGrafica::where('oms_catalogo_grafica_id', $this->graficaId)
                ->orderBy('x_value')
                ->paginate(20),
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'oms-datos-modal');
    }

    public function save(CatalogServiceContract $service): void
    {
        $this->validate([
            'xValue' => 'required|numeric|min:0',
            'lValue' => 'required|numeric',
            'mValue' => 'required|numeric|min:0',
            'sValue' => 'required|numeric|min:0',
            'sd3neg' => 'nullable|numeric',
            'sd2neg' => 'nullable|numeric',
            'sd1neg' => 'nullable|numeric',
            'sd0' => 'nullable|numeric',
            'sd1' => 'nullable|numeric',
            'sd2' => 'nullable|numeric',
            'sd3' => 'nullable|numeric',
            'p3' => 'nullable|numeric',
            'p15' => 'nullable|numeric',
            'p50' => 'nullable|numeric',
            'p85' => 'nullable|numeric',
            'p97' => 'nullable|numeric',
        ]);

        $dto = new OmsDatoGraficaDTO(
            id: $this->editingId,
            oms_catalogo_grafica_id: $this->graficaId,
            x_value: (float) $this->xValue,
            l_value: (float) $this->lValue,
            m_value: (float) $this->mValue,
            s_value: (float) $this->sValue,
            sd3neg: $this->sd3neg !== '' ? (float) $this->sd3neg : null,
            sd2neg: $this->sd2neg !== '' ? (float) $this->sd2neg : null,
            sd1neg: $this->sd1neg !== '' ? (float) $this->sd1neg : null,
            sd0: $this->sd0 !== '' ? (float) $this->sd0 : null,
            sd1: $this->sd1 !== '' ? (float) $this->sd1 : null,
            sd2: $this->sd2 !== '' ? (float) $this->sd2 : null,
            sd3: $this->sd3 !== '' ? (float) $this->sd3 : null,
            p3: $this->p3 !== '' ? (float) $this->p3 : null,
            p15: $this->p15 !== '' ? (float) $this->p15 : null,
            p50: $this->p50 !== '' ? (float) $this->p50 : null,
            p85: $this->p85 !== '' ? (float) $this->p85 : null,
            p97: $this->p97 !== '' ? (float) $this->p97 : null,
        );

        if ($this->editingId) {
            $service->updateOmsDato($this->editingId, $dto);
        } else {
            $service->createOmsDato($dto);
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'oms-datos-modal');
    }

    public function edit(string $id): void
    {
        $dato = OmsDatoGrafica::findOrFail($id);
        $this->editingId = $dato->id;
        $this->xValue = (string) $dato->x_value;
        $this->lValue = (string) $dato->l_value;
        $this->mValue = (string) $dato->m_value;
        $this->sValue = (string) $dato->s_value;
        $this->sd3neg = $dato->sd3neg !== null ? (string) $dato->sd3neg : '';
        $this->sd2neg = $dato->sd2neg !== null ? (string) $dato->sd2neg : '';
        $this->sd1neg = $dato->sd1neg !== null ? (string) $dato->sd1neg : '';
        $this->sd0 = $dato->sd0 !== null ? (string) $dato->sd0 : '';
        $this->sd1 = $dato->sd1 !== null ? (string) $dato->sd1 : '';
        $this->sd2 = $dato->sd2 !== null ? (string) $dato->sd2 : '';
        $this->sd3 = $dato->sd3 !== null ? (string) $dato->sd3 : '';
        $this->p3 = $dato->p3 !== null ? (string) $dato->p3 : '';
        $this->p15 = $dato->p15 !== null ? (string) $dato->p15 : '';
        $this->p50 = $dato->p50 !== null ? (string) $dato->p50 : '';
        $this->p85 = $dato->p85 !== null ? (string) $dato->p85 : '';
        $this->p97 = $dato->p97 !== null ? (string) $dato->p97 : '';
        $this->dispatch('modal-show', name: 'oms-datos-modal');
    }

    public function delete(string $id, CatalogServiceContract $service): void
    {
        $service->deleteOmsDato($id);
    }

    public function resetForm(): void
    {
        $this->reset([
            'xValue',
            'lValue',
            'mValue',
            'sValue',
            'sd3neg',
            'sd2neg',
            'sd1neg',
            'sd0',
            'sd1',
            'sd2',
            'sd3',
            'p3',
            'p15',
            'p50',
            'p85',
            'p97',
            'editingId',
        ]);
    }
}; ?>

<section class="p-6">
    <div class="mb-4">
        <flux:link :href="route('settings.catalogs.oms-graficas')" icon="arrow-left">
            {{ __('Volver a Gráficas OMS') }}
        </flux:link>
    </div>

    <div class="mb-6 flex justify-between items-end">
        <div>
            <flux:heading size="xl">{{ $graficaNombre }}</flux:heading>
            <flux:subheading>
                {{ __('Código:') }}
                <span class="font-mono">{{ $graficaCodigo }}</span>
                &mdash; {{ __('Datos LMS y curvas de referencia') }}
            </flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openCreateModal" dusk="btn-nuevo-dato">
            {{ __('Nuevo Dato') }}
        </flux:button>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 shadow-md rounded-lg overflow-hidden border border-gray-100 dark:border-zinc-800"
    >
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                <thead class="bg-gray-50 dark:bg-zinc-800">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            {{ __('X') }}
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            L
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            M
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            S
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            -3 SD
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            {{ __('Mediana') }}
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            +3 SD
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            P3
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            P50
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            P97
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                        >
                            {{ __('Acciones') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach ($datos as $dato)
                        <tr wire:key="{{ $dato->id }}">
                            <td class="px-4 py-3 font-mono font-medium">{{ $dato->x_value }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">
                                {{ $dato->l_value }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">
                                {{ $dato->m_value }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">
                                {{ $dato->s_value }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $dato->sd3neg ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $dato->sd0 ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $dato->sd3 ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $dato->p3 ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $dato->p50 ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $dato->p97 ?? '—' }}</td>
                            <td class="px-4 py-3 flex gap-2">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil"
                                    wire:click="edit('{{ $dato->id }}')"
                                />
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    wire:click="delete('{{ $dato->id }}')"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
            {{ $datos->links() }}
        </div>
    </div>

    <!-- Modal -->
    <flux:modal name="oms-datos-modal" class="md:w-[700px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Editar Dato LMS') : __('Nuevo Dato LMS') }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Valores del método LMS para el punto de la curva OMS.') }}
                </flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                {{-- LMS requeridos --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        type="number"
                        step="0.0001"
                        wire:model="xValue"
                        :label="__('Valor X (edad meses / longitud cm)')"
                        required
                    />
                    <flux:input
                        type="number"
                        step="0.000001"
                        wire:model="lValue"
                        :label="__('L (Box-Cox power)')"
                        required
                    />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="number" step="0.000001" wire:model="mValue" :label="__('M (Mediana)')" required />
                    <flux:input
                        type="number"
                        step="0.000001"
                        wire:model="sValue"
                        :label="__('S (Coef. variación)')"
                        required
                    />
                </div>

                {{-- SD Values --}}
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Valores SD (opcionales)') }}
                    </p>
                    <div class="grid grid-cols-4 gap-3">
                        <flux:input type="number" step="0.0001" wire:model="sd3neg" label="-3 SD" />
                        <flux:input type="number" step="0.0001" wire:model="sd2neg" label="-2 SD" />
                        <flux:input type="number" step="0.0001" wire:model="sd1neg" label="-1 SD" />
                        <flux:input type="number" step="0.0001" wire:model="sd0" label="0 (Med.)" />
                    </div>
                    <div class="grid grid-cols-3 gap-3 mt-3">
                        <flux:input type="number" step="0.0001" wire:model="sd1" label="+1 SD" />
                        <flux:input type="number" step="0.0001" wire:model="sd2" label="+2 SD" />
                        <flux:input type="number" step="0.0001" wire:model="sd3" label="+3 SD" />
                    </div>
                </div>

                {{-- Percentiles --}}
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Percentiles (opcionales)') }}
                    </p>
                    <div class="grid grid-cols-5 gap-3">
                        <flux:input type="number" step="0.0001" wire:model="p3" label="P3" />
                        <flux:input type="number" step="0.0001" wire:model="p15" label="P15" />
                        <flux:input type="number" step="0.0001" wire:model="p50" label="P50" />
                        <flux:input type="number" step="0.0001" wire:model="p85" label="P85" />
                        <flux:input type="number" step="0.0001" wire:model="p97" label="P97" />
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" dusk="btn-guardar-dato">
                        {{ __('Guardar') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
