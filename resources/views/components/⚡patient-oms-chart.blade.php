<?php

use App\Contracts\GrowthChartServiceContract;
use App\Models\OmsCatalogoGrafica;
use App\Models\Patient;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $patientId;

    /** @var array<int, array{id: string, nombre: string, tipo_grafica: string, rango_edad: string|null}> */
    public array $graficas = [];

    public string $selectedGraficaId = '';

    public string $mode = 'padres';

    public string $error = '';

    /**
     * Computed property — NO viaja en el payload de Livewire,
     * se recalcula en cada render y se cachea dentro del request.
     *
     * @return array{grafica: array{id: string, nombre: string, tipo_grafica: string}, labels: array<int, float>, reference_datasets: array<int, array{label: string, color: string, data: array<int, float|null>}>, percentile_datasets: array<int, array{label: string, color: string, dash: bool, data: array<int, float|null>}>, patient_datapoints: array<int, array{x: float, y: float, z_score: float, category: string, date: string}>}|null
     */
    #[Computed]
    public function chartData(): ?array
    {
        if ($this->selectedGraficaId === '') {
            return null;
        }

        try {
            /** @var GrowthChartServiceContract $service */
            $service = app(GrowthChartServiceContract::class);

            return $service->prepareChartData($this->patientId, $this->selectedGraficaId);
        } catch (\Throwable) {
            return null;
        }
    }

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
        }
    }

    public function updatedSelectedGraficaId(): void
    {
        $this->error = '';
        $data = $this->chartData;

        if ($data) {
            $this->dispatch(
                'oms-chart-data',
                data: $data,
                xLabel: $this->getXLabel(),
                yLabel: $this->getYLabel(),
                mode: $this->mode,
            );
        } else {
            $this->error = 'No hay datos de referencia OMS para esta gráfica.';
        }
    }

    public function updatedMode(): void
    {
        $data = $this->chartData;

        if ($data) {
            $this->dispatch('oms-chart-mode', mode: $this->mode);
        }
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
            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tipo de Gráfica OMS</span>
            <div class="flex flex-wrap gap-2" dusk="grafica-selector">
                @foreach ($graficas as $g)
                    <label class="relative cursor-pointer" wire:key="grafica-{{ $g['id'] }}">
                        <input
                            type="radio"
                            name="grafica"
                            value="{{ $g['id'] }}"
                            wire:model.live="selectedGraficaId"
                            class="peer sr-only"
                        />
                        <div
                            class="rounded-lg border px-3 py-2 text-xs font-medium transition peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:text-teal-700 peer-checked:ring-1 peer-checked:ring-teal-500 dark:peer-checked:border-teal-400 dark:peer-checked:bg-teal-900/30 dark:peer-checked:text-teal-300 border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/50"
                        >
                            {{ $g['nombre'] }}
                            @if ($g['rango_edad'])
                                <span class="ml-1 text-[10px] opacity-60">({{ $g['rango_edad'] }})</span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
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

    @if ($this->chartData !== null)
        {{-- Toggle de modo: Padres (default) / Médico --}}
        <div class="flex items-center gap-2 mb-3" dusk="mode-toggle">
            <span class="text-xs text-gray-500 dark:text-gray-400">Vista:</span>
            <label class="cursor-pointer">
                <input type="radio" name="mode" value="padres" wire:model.live="mode" class="peer sr-only" />
                <div
                    class="px-3 py-1 rounded-md text-xs font-medium transition peer-checked:bg-teal-600 peer-checked:text-white peer-checked:shadow bg-gray-100 dark:bg-zinc-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600"
                    dusk="btn-modo-padres"
                >
                    Padres
                </div>
            </label>
            <label class="cursor-pointer">
                <input type="radio" name="mode" value="medico" wire:model.live="mode" class="peer sr-only" />
                <div
                    class="px-3 py-1 rounded-md text-xs font-medium transition peer-checked:bg-teal-600 peer-checked:text-white peer-checked:shadow bg-gray-100 dark:bg-zinc-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600"
                    dusk="btn-modo-medico"
                >
                    Médico
                </div>
            </label>
        </div>

        {{-- Gráfica Chart.js interactiva --}}
        <div
            x-data="omsChart(@js($this->chartData), @js($this->getXLabel()), @js($this->getYLabel()), @js($this->mode))"
            @oms-chart-data.window="render($event.detail.data, $event.detail.xLabel, $event.detail.yLabel, $event.detail.mode || 'padres')"
            @oms-chart-mode.window="setMode($event.detail.mode)"
            wire:ignore
            class="mb-6"
            dusk="chart-wrapper"
        >
            <div class="relative rounded-xl border border-gray-200 dark:border-zinc-700" style="height: 360px">
                <canvas dusk="chart-canvas"></canvas>
            </div>
        </div>

        {{-- Datos del paciente en tabla --}}
        @if (count($this->chartData['patient_datapoints']) > 0)
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
                            @foreach ($this->chartData['patient_datapoints'] as $point)
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

@assets
    @vite('resources/js/chart.js')
@endassets

@script
    <script>
        // Conversión z_score → percentil aproximado (polinomio de Horner, normal CDF)
        function zToPercentile(z) {
            const t = 1 / (1 + 0.2316419 * Math.abs(z));
            const d = 0.3989423 * Math.exp((-z * z) / 2);
            const p =
                d * t * (0.31938153 + t * (-0.356563782 + t * (1.781477937 + t * (-1.821255978 + t * 1.330274429))));
            return z >= 0 ? Math.round((1 - p) * 100) : Math.round(p * 100);
        }

        Alpine.data('omsChart', (initialData, xLabel, yLabel, initialMode) => ({
            chart: null,
            _mode: initialMode || 'padres',
            _data: null,
            _xL: null,
            _yL: null,

            init() {
                if (initialData) {
                    this.$nextTick(() => this.render(initialData, xLabel, yLabel, this._mode));
                }
            },

            destroy() {
                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }
            },

            setMode(m) {
                this._mode = m;
                if (this._data) {
                    this.render(this._data, this._xL, this._yL, m);
                }
            },

            render(data, xL, yL, mode) {
                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }

                this._data = data;
                this._xL = xL;
                this._yL = yL;
                if (mode) this._mode = mode;

                const canvas = this.$el.querySelector('[dusk="chart-canvas"]');
                if (!canvas || !data || !canvas.isConnected) return;

                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                const referenceDs =
                    this._mode === 'medico'
                        ? data.reference_datasets.map((ds) => ({
                              type: 'line',
                              label: ds.label,
                              data: ds.data,
                              borderColor: ds.color,
                              backgroundColor: 'transparent',
                              borderWidth: ds.label === 'Mediana' ? 2 : 1,
                              borderDash: ds.label === '-3 DS' || ds.label === '+3 DS' ? [4, 4] : [],
                              pointRadius: 0,
                              tension: 0.3,
                          }))
                        : data.percentile_datasets.map((ds) => ({
                              type: 'line',
                              label: ds.label,
                              data: ds.data,
                              borderColor: ds.color,
                              backgroundColor: 'transparent',
                              borderWidth: ds.dash ? 1 : 2,
                              borderDash: ds.dash ? [5, 5] : [],
                              pointRadius: 0,
                              tension: 0.4,
                          }));

                const patientDs = {
                    type: 'scatter',
                    label: 'Paciente',
                    data: data.patient_datapoints.map((p) => ({
                        x: p.x,
                        y: p.y,
                        z_score: p.z_score,
                        category: p.category,
                        date: p.date,
                    })),
                    backgroundColor: '#0d9488',
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                };

                this.chart = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [...referenceDs, patientDs],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    filter: (item) => item.text !== 'Paciente',
                                },
                            },
                            tooltip: {
                                backgroundColor: 'rgba(30, 41, 59, 0.92)',
                                titleFont: { size: 13 },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 6,
                                callbacks: {
                                    label(ctx) {
                                        if (ctx.dataset.type === 'scatter') {
                                            const p = ctx.raw;
                                            if (ctx.chart.config.data.datasets.some((d) => d.label === '-3 DS')) {
                                                return [
                                                    `${xL}: ${p.x}`,
                                                    `${yL}: ${p.y}`,
                                                    `Z-Score: ${p.z_score} (${p.category})`,
                                                    `Fecha: ${p.date}`,
                                                ];
                                            }
                                            return [
                                                `${yL}: ${p.y}`,
                                                `~Percentil ${zToPercentile(p.z_score)}`,
                                                `Fecha: ${p.date}`,
                                            ];
                                        }
                                        return `${ctx.dataset.label}: ${ctx.parsed.y?.toFixed(2)}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                title: {
                                    display: true,
                                    text: xL,
                                },
                            },
                            y: {
                                grid: {
                                    color: '#f3f4f620',
                                },
                                title: {
                                    display: true,
                                    text: yL,
                                },
                            },
                        },
                    },
                });
            },
        }));
    </script>
@endscript
