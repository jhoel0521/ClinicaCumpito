<?php

use App\Contracts\GrowthChartServiceContract;
use App\Models\OmsCatalogoGrafica;
use App\Models\OmsDatoGrafica;
use App\Models\Patient;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $patientId;

    /** @var array<int, array{id: string, nombre: string, tipo_grafica: string, rango_edad: string|null, max_x: float}> */
    public array $graficas = [];

    public string $selectedGraficaId = '';

    public string $mode = 'padres';

    /** peso | talla | perimetro | imc */
    public string $filterTipo = 'peso';

    /** 0-6m | 0-2a | 0-5a | 0-13a */
    public string $filterEdad = '0-5a';

    public string $error = '';

    public bool $showAllPoints = false;

    private const TIPO_MAP = [
        'peso' => 'peso_edad',
        'talla' => 'talla_edad',
        'perimetro' => 'perimetro_cefalico',
        'imc' => 'imc',
    ];

    private const EDAD_RANGES = [
        '0-6m' => ['label' => '0 a 6 meses', 'max' => 6],
        '0-2a' => ['label' => '0 a 2 años', 'max' => 24],
        '0-5a' => ['label' => '0 a 5 años', 'max' => 60],
        '0-13a' => ['label' => '0 a 13 años', 'max' => 156],
    ];

    /**
     * @return array{grafica: array{id: string, nombre: string, tipo_grafica: string}, labels: array<int, float>, reference_datasets: array<int, mixed>, percentile_datasets: array<int, mixed>, patient_datapoints: array<int, mixed>}|null
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

    /** @return array<string, array{label: string, tipo_grafica: string}> */
    #[Computed]
    public function availableTipos(): array
    {
        $tiposEnDB = collect($this->graficas)
            ->pluck('tipo_grafica')
            ->unique()
            ->values()
            ->all();

        $all = [
            'peso' => ['label' => 'Peso', 'tipo_grafica' => 'peso_edad'],
            'talla' => ['label' => 'Talla', 'tipo_grafica' => 'talla_edad'],
            'perimetro' => ['label' => 'Perímetro', 'tipo_grafica' => 'perimetro_cefalico'],
            'imc' => ['label' => 'IMC', 'tipo_grafica' => 'imc'],
        ];

        return collect($all)
            ->filter(fn ($v) => in_array($v['tipo_grafica'], $tiposEnDB))
            ->all();
    }

    /** @return array<string, array{label: string, max: int}> */
    #[Computed]
    public function availableEdadRanges(): array
    {
        $grafica = collect($this->graficas)->firstWhere('id', $this->selectedGraficaId);
        $maxX = (float) ($grafica['max_x'] ?? 60);

        return collect(self::EDAD_RANGES)
            ->filter(fn ($r) => $r['max'] <= $maxX)
            ->all();
    }

    public function currentMaxX(): int
    {
        return self::EDAD_RANGES[$this->filterEdad]['max'] ?? 60;
    }

    public function mount(string $patientId): void
    {
        $this->patientId = $patientId;

        $patient = Patient::findOrFail($patientId);
        $graficasCollection = OmsCatalogoGrafica::query()
            ->where('sexo', $patient->gender?->value())
            ->orderBy('nombre')
            ->get();

        $maxXPerGrafica = OmsDatoGrafica::query()
            ->whereIn('oms_catalogo_grafica_id', $graficasCollection->pluck('id'))
            ->groupBy('oms_catalogo_grafica_id')
            ->selectRaw('oms_catalogo_grafica_id, MAX(x_value) as max_x')
            ->pluck('max_x', 'oms_catalogo_grafica_id');

        $this->graficas = $graficasCollection
            ->map(
                fn (OmsCatalogoGrafica $g) => [
                    'id' => $g->id,
                    'nombre' => $g->nombre,
                    'tipo_grafica' => $g->tipo_grafica,
                    'rango_edad' => $g->rango_edad,
                    'max_x' => (float) ($maxXPerGrafica[$g->id] ?? 60),
                ],
            )
            ->values()
            ->all();

        $firstTipo = array_key_first($this->availableTipos);
        if ($firstTipo) {
            $this->filterTipo = $firstTipo;
        }

        $this->syncGraficaFromFilters();
        $this->clampEdadFilter();
    }

    public function updatedFilterTipo(): void
    {
        $this->syncGraficaFromFilters();
        $this->clampEdadFilter();
        $this->dispatchChartData();
    }

    public function updatedFilterEdad(): void
    {
        $this->dispatch('oms-chart-range', maxX: $this->currentMaxX());
    }

    public function updatedMode(): void
    {
        $data = $this->chartData;
        if ($data) {
            $this->dispatch('oms-chart-mode', mode: $this->mode);
        }
    }

    private function syncGraficaFromFilters(): void
    {
        $tipoGrafica = self::TIPO_MAP[$this->filterTipo] ?? 'peso_edad';
        $grafica = collect($this->graficas)->firstWhere('tipo_grafica', $tipoGrafica);
        if ($grafica) {
            $this->selectedGraficaId = $grafica['id'];
        }
    }

    private function clampEdadFilter(): void
    {
        $available = array_keys($this->availableEdadRanges);
        if (! empty($available) && ! in_array($this->filterEdad, $available)) {
            $this->filterEdad = end($available);
        }
    }

    private function dispatchChartData(): void
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
                maxX: $this->currentMaxX(),
            );
        } else {
            $this->error = 'No hay datos de referencia OMS para esta gráfica.';
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
    {{-- Sin gráficas configuradas --}}
    @if (count($graficas) === 0)
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
    @else
        {{--
            ══════════════════════════════════════════════════════════
            3 FILTROS
            ══════════════════════════════════════════════════════════
        --}}
        <div class="mb-6 space-y-4 flex gap-4" dusk="oms-filters">
            {{-- Filtro 1: Tipo de Medición --}}
            <div>
                <span
                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                >
                    Medición
                </span>
                <div class="flex flex-wrap gap-2" dusk="filter-tipo">
                    @foreach ($this->availableTipos as $key => $tipo)
                        <label class="cursor-pointer" wire:key="tipo-{{ $key }}">
                            <input
                                type="radio"
                                name="filterTipo"
                                value="{{ $key }}"
                                wire:model.live="filterTipo"
                                class="peer sr-only"
                            />
                            <div
                                class="rounded-lg border px-4 py-2 text-sm font-medium transition peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:text-teal-700 peer-checked:ring-1 peer-checked:ring-teal-500 dark:peer-checked:border-teal-400 dark:peer-checked:bg-teal-900/30 dark:peer-checked:text-teal-300 border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/50"
                            >
                                {{ $tipo['label'] }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Filtro 2: Rango de Edad --}}
            <div>
                <span
                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                >
                    Rango de Edad
                </span>
                <div class="flex flex-wrap gap-2" dusk="filter-edad">
                    @foreach ($this->availableEdadRanges as $key => $range)
                        <label class="cursor-pointer" wire:key="edad-{{ $key }}">
                            <input
                                type="radio"
                                name="filterEdad"
                                value="{{ $key }}"
                                wire:model.live="filterEdad"
                                class="peer sr-only"
                            />
                            <div
                                class="rounded-lg border px-4 py-2 text-sm font-medium transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:ring-1 peer-checked:ring-indigo-500 dark:peer-checked:border-indigo-400 dark:peer-checked:bg-indigo-900/30 dark:peer-checked:text-indigo-300 border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/50"
                            >
                                {{ $range['label'] }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Filtro 3: Vista --}}
            <div>
                <span
                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                >
                    Vista
                </span>
                <div class="flex gap-2" dusk="filter-vista">
                    <label class="cursor-pointer">
                        <input type="radio" name="mode" value="padres" wire:model.live="mode" class="peer sr-only" />
                        <div
                            class="rounded-lg border px-4 py-2 text-sm font-medium transition peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-700 peer-checked:ring-1 peer-checked:ring-violet-500 dark:peer-checked:border-violet-400 dark:peer-checked:bg-violet-900/30 dark:peer-checked:text-violet-300 border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/50"
                            dusk="btn-modo-padres"
                        >
                            Padres
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="mode" value="medico" wire:model.live="mode" class="peer sr-only" />
                        <div
                            class="rounded-lg border px-4 py-2 text-sm font-medium transition peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-700 peer-checked:ring-1 peer-checked:ring-violet-500 dark:peer-checked:border-violet-400 dark:peer-checked:bg-violet-900/30 dark:peer-checked:text-violet-300 border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/50"
                            dusk="btn-modo-medico"
                        >
                            Médico
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Error --}}
        @if ($error)
            <div
                class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-sm text-red-700 dark:text-red-300 mb-4"
            >
                {{ $error }}
            </div>
        @endif

        @if ($this->chartData !== null)
            {{-- Gráfica Chart.js --}}
            <div
                x-data="omsChart(@js($this->chartData), @js($this->getXLabel()), @js($this->getYLabel()), @js($this->mode), @js($this->currentMaxX()))"
                @oms-chart-data.window="render($event.detail.data, $event.detail.xLabel, $event.detail.yLabel, $event.detail.mode || 'padres', $event.detail.maxX ?? null)"
                @oms-chart-mode.window="setMode($event.detail.mode)"
                @oms-chart-range.window="setRange($event.detail.maxX)"
                wire:ignore
                class="mb-6"
                dusk="chart-wrapper"
            >
                <div class="relative rounded-xl border border-gray-200 dark:border-zinc-700" style="height: 360px">
                    <canvas dusk="chart-canvas"></canvas>
                </div>
            </div>

            {{-- Tabla de datos del paciente --}}
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
                                @foreach ($this->showAllPoints ? $this->chartData['patient_datapoints'] : array_slice($this->chartData['patient_datapoints'], 0, 5) as $point)
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
                    @if (count($this->chartData['patient_datapoints']) > 5)
                        <div class="mt-2 text-center">
                            <flux:button size="sm" variant="ghost" wire:click="$toggle('showAllPoints')">
                                @if ($this->showAllPoints)
                                    Ver menos
                                @else
                                    Ver todos los {{ count($this->chartData['patient_datapoints']) }} puntos
                                @endif
                            </flux:button>
                        </div>
                    @endif
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
    @endif
</div>

@assets
    @vite('resources/js/chart.js')
@endassets

@script
    <script>
        function zToPercentile(z) {
            const t = 1 / (1 + 0.2316419 * Math.abs(z));
            const d = 0.3989423 * Math.exp((-z * z) / 2);
            const p =
                d * t * (0.31938153 + t * (-0.356563782 + t * (1.781477937 + t * (-1.821255978 + t * 1.330274429))));
            return z >= 0 ? Math.round((1 - p) * 100) : Math.round(p * 100);
        }

        Alpine.data('omsChart', (initialData, xLabel, yLabel, initialMode, initialMaxX) => ({
            // La instancia de Chart.js se almacena en this.$el._chart (DOM, fuera del
            // estado reactivo de Alpine) para evitar que Livewire la envuelva en un Proxy
            // y cause "Maximum call stack size exceeded" al recorrer los objetos internos.
            _mode: initialMode || 'padres',
            _data: null,
            _xL: null,
            _yL: null,
            _maxX: initialMaxX || null,

            init() {
                if (initialData) {
                    this.$nextTick(() => this.render(initialData, xLabel, yLabel, this._mode, initialMaxX));
                }
            },

            destroy() {
                const c = this.$el._chart;
                if (c) {
                    c.destroy();
                    this.$el._chart = null;
                }
            },

            setMode(m) {
                this._mode = m;
                if (this._data) {
                    this.render(this._data, this._xL, this._yL, m, this._maxX);
                }
            },

            setRange(maxX) {
                this._maxX = maxX;
                const c = this.$el._chart;
                if (c) {
                    c.options.scales.x.max = maxX;
                    c.update('none');
                }
            },

            render(data, xL, yL, mode, maxX) {
                const existing = this.$el._chart;
                if (existing) {
                    existing.destroy();
                    this.$el._chart = null;
                }

                this._data = data;
                this._xL = xL;
                this._yL = yL;
                if (mode) this._mode = mode;
                if (maxX !== undefined && maxX !== null) this._maxX = maxX;

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

                const xAxisConfig = {
                    grid: {
                        display: false,
                    },
                    title: {
                        display: true,
                        text: xL,
                    },
                };
                if (this._maxX !== null) {
                    xAxisConfig.max = this._maxX;
                }

                this.$el._chart = new Chart(canvas, {
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
                                titleFont: {
                                    size: 13,
                                },
                                bodyFont: {
                                    size: 12,
                                },
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
                            x: xAxisConfig,
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
