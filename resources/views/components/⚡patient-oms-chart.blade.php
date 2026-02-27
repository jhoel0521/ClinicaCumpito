<?php

use App\Contracts\GrowthChartServiceContract;
use App\Models\OmsCatalogoGrafica;
use App\Models\Patient;
use Livewire\Component;

new class extends Component {
    public string $patientId;

    /** @var array<int, array{id: string, nombre: string, tipo_grafica: string, rango_edad: string|null}> */
    public array $graficas = [];

    public string $selectedGraficaId = '';

    /** @var array{grafica: array{id: string, nombre: string, tipo_grafica: string}, labels: array<int, float>, reference_datasets: array<int, array{label: string, color: string, data: array<int, float|null>}>, patient_datapoints: array<int, array{x: float, y: float, z_score: float, category: string, date: string}>}|null */
    public ?array $chartData = null;

    public bool $loading = false;

    public string $error = '';

    public function mount(string $patientId): void
    {
        $this->patientId = $patientId;

        $patient = Patient::findOrFail($patientId);
        $graficasCollection = OmsCatalogoGrafica::query()
            ->where('sexo', $patient->gender?->value())
            ->orderBy('nombre')
            ->get();

        $this->graficas = $graficasCollection
            ->map(
                fn (OmsCatalogoGrafica $g) => [
                    'id' => $g->id,
                    'nombre' => $g->nombre,
                    'tipo_grafica' => $g->tipo_grafica,
                    'rango_edad' => $g->rango_edad,
                ],
            )
            ->values()
            ->all();

        $first = $graficasCollection->first();
        if ($first) {
            $this->selectedGraficaId = $first->id;
            $this->loadChart();
        }
    }

    public function updatedSelectedGraficaId(): void
    {
        $this->error = '';
        $this->chartData = null;
        $this->loadChart();
    }

    public function loadChart(): void
    {
        if ($this->selectedGraficaId === '') {
            return;
        }

        try {
            $service = app(GrowthChartServiceContract::class);
            $this->chartData = $service->prepareChartData($this->patientId, $this->selectedGraficaId);
        } catch (\Throwable $e) {
            $this->error = 'No hay datos de referencia OMS para esta gráfica.';
            $this->chartData = null;
        }
    }

    /** @return array<string, string> */
    private function categoryColors(): array
    {
        return [
            'Normal' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            'Riesgo' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
            'Alerta' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
            'Crítico' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        ];
    }

    public function getXLabel(): string
    {
        $tipo = collect($this->graficas)->firstWhere('id', $this->selectedGraficaId)['tipo_grafica'] ?? '';

        return match ($tipo) {
            'peso_talla' => 'Talla (cm)',
            default => 'Edad (meses)',
        };
    }

    public function getYLabel(): string
    {
        $tipo = collect($this->graficas)->firstWhere('id', $this->selectedGraficaId)['tipo_grafica'] ?? '';

        return match ($tipo) {
            'talla_edad' => 'Talla (cm)',
            'peso_edad', 'peso_talla' => 'Peso (kg)',
            'perimetro_cefalico' => 'Perímetro cefálico (cm)',
            'imc' => 'IMC (kg/m²)',
            default => 'Valor',
        };
    }
}; ?>

<div dusk="growth-chart-panel">
    {{-- Selector de Gráfica --}}
    @if (count($graficas) > 0)
        <div class="mb-6">
            <label for="grafica-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tipo de Gráfica OMS
            </label>
            <select
                id="grafica-select"
                wire:model.live="selectedGraficaId"
                dusk="grafica-selector"
                class="block w-full max-w-sm rounded-lg border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
            >
                @foreach ($graficas as $g)
                    <option value="{{ $g['id'] }}">
                        {{ $g['nombre'] }}
                        @if ($g['rango_edad'])
                            ({{ $g['rango_edad'] }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
    @else
        <div class="rounded-lg bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 p-8 text-center">
            <svg
                class="mx-auto h-10 w-10 text-gray-300 dark:text-zinc-600 mb-3"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">No hay gráficas OMS configuradas para este paciente.</p>
        </div>
    @endif

    {{-- Error --}}
    @if ($error)
        <div
            class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-sm text-red-700 dark:text-red-300 mb-4"
        >
            {{ $error }}
        </div>
    @endif

    @if ($chartData !== null)
        {{-- Placeholder Chart.js (Fase 7) --}}
        <div
            class="mb-6 rounded-xl border border-dashed border-gray-300 dark:border-zinc-600 bg-gray-50 dark:bg-zinc-800/50 flex items-center justify-center"
            style="height: 320px"
            dusk="chart-canvas-placeholder"
        >
            <div class="text-center text-gray-400 dark:text-zinc-500">
                <svg class="mx-auto h-12 w-12 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
                    />
                </svg>
                <p class="text-sm font-medium">Gráfica interactiva</p>
                <p class="text-xs mt-1">Disponible en Fase 7</p>
            </div>
        </div>

        {{-- Datos del paciente en tabla --}}
        @if (count($chartData['patient_datapoints']) > 0)
            <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    Puntos del Paciente —
                    <span class="font-normal text-gray-500">
                        Eje X: {{ $this->getXLabel() }} · Eje Y: {{ $this->getYLabel() }}
                    </span>
                </h4>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-zinc-700">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700 text-sm"
                        dusk="chart-datapoints-table"
                    >
                        <thead class="bg-gray-50 dark:bg-zinc-800">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                >
                                    Fecha
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                >
                                    {{ $this->getXLabel() }}
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                >
                                    {{ $this->getYLabel() }}
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                >
                                    Z-Score
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                >
                                    Categoría
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-100 dark:divide-zinc-800">
                            @foreach ($chartData['patient_datapoints'] as $point)
                                @php
                                    $colors = [
                                        'Normal' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                        'Riesgo' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                        'Alerta' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                                        'Crítico' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                    ];
                                    $badgeClass = $colors[$point['category']] ?? 'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300';
                                @endphp

                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($point['date'])->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-900 dark:text-gray-100 font-medium">
                                        {{ number_format($point['x'], 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-900 dark:text-gray-100 font-medium">
                                        {{ number_format($point['y'], 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                        {{ number_format($point['z_score'], 2) }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}"
                                        >
                                            {{ $point['category'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div
                class="rounded-lg bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 p-6 text-center"
            >
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    El paciente aún no tiene mediciones registradas para esta gráfica.
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    Los datos aparecerán aquí cuando se registren signos vitales en consultas.
                </p>
            </div>
        @endif
    @endif
</div>
