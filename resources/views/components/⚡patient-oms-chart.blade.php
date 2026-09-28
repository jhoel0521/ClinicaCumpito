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

    /** peso | talla | perimetro */
    public string $filterTipo = 'peso';

    /** 0-6m | 0-2a | 0-5a | 0-13a */
    public string $filterEdad = '0-5a';

    public string $error = '';

    /** Edad actual del paciente en meses (null si no tiene fecha de nacimiento). */
    public ?int $patientAgeMonths = null;

    private const TIPO_MAP = [
        'peso' => 'peso_edad',
        'talla' => 'talla_edad',
        'perimetro' => 'perimetro_cefalico',
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

    /**
     * Aviso cuando el paciente no puede graficarse en la boleta seleccionada:
     * falta su fecha de nacimiento o su edad supera el rango OMS disponible.
     */
    #[Computed]
    public function rangeNotice(): ?string
    {
        if ($this->patientAgeMonths === null) {
            return 'Registra la fecha de nacimiento del paciente para ubicar sus mediciones en la gráfica.';
        }

        $grafica = collect($this->graficas)->firstWhere('id', $this->selectedGraficaId);
        $maxX = (int) round((float) ($grafica['max_x'] ?? 60));

        if ($this->patientAgeMonths > $maxX) {
            return "El paciente tiene {$this->patientAgeMonths} meses y esta boleta OMS solo cubre hasta {$maxX} meses. Sus mediciones no se grafican para evitar lecturas clínicas erróneas.";
        }

        return null;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="rounded-lg border border-gray-200 dark:border-zinc-700 p-8 text-center text-sm text-gray-500 dark:text-gray-400">
            Cargando gráfica de crecimiento…
        </div>
        HTML;
    }

    public function mount(string $patientId): void
    {
        $this->patientId = $patientId;

        $patient = Patient::findOrFail($patientId);

        $this->patientAgeMonths = $patient->date_of_birth
            ? (int) \Carbon\Carbon::parse($patient->date_of_birth)->diffInMonths(now())
            : null;

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

    // `mode` y `filterEdad` se sincronizan diferidos (sin request propio): el
    // cambio visual lo hace Alpine en el navegador y el valor llega al servidor
    // con el próximo request (p. ej. al cambiar la medición).

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
                                wire:model="filterEdad"
                                x-on:change="$dispatch('oms-chart-range', { maxX: {{ $range['max'] }} })"
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
                        {{--
                            Vista y rango se resuelven en el navegador: los datos ya
                            están cargados y con internet lento cada viaje al
                            servidor re-enviaba toda la gráfica.
                        --}}
                        <input
                            type="radio"
                            name="mode"
                            value="padres"
                            wire:model="mode"
                            x-on:change="$dispatch('oms-chart-mode', { mode: 'padres' })"
                            class="peer sr-only"
                        />
                        <div
                            class="rounded-lg border px-4 py-2 text-sm font-medium transition peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-700 peer-checked:ring-1 peer-checked:ring-violet-500 dark:peer-checked:border-violet-400 dark:peer-checked:bg-violet-900/30 dark:peer-checked:text-violet-300 border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/50"
                            dusk="btn-modo-padres"
                        >
                            Padres
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input
                            type="radio"
                            name="mode"
                            value="medico"
                            wire:model="mode"
                            x-on:change="$dispatch('oms-chart-mode', { mode: 'medico' })"
                            class="peer sr-only"
                        />
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

        {{-- Aviso de paciente fuera de rango OMS --}}
        @if ($this->rangeNotice)
            <div
                dusk="oms-range-notice"
                class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4 text-sm text-amber-800 dark:text-amber-300 mb-4 flex items-start gap-2"
            >
                <flux:icon.exclamation-triangle class="size-4 mt-0.5 shrink-0" />
                <span>{{ $this->rangeNotice }}</span>
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

                {{-- Texto explicativo para la doctora y los padres (se actualiza con Alpine). --}}
                <div class="mt-3 space-y-1" dusk="oms-chart-summary">
                    <p
                        x-show="lastPointText()"
                        x-text="lastPointText()"
                        class="text-sm font-medium text-teal-700 dark:text-teal-300"
                    ></p>
                    <p x-show="! lastPointText()" class="text-sm text-gray-500 dark:text-gray-400">
                        Todavía no hay mediciones del paciente en esta boleta.
                    </p>
                    <p x-text="legendText()" class="text-xs text-gray-500 dark:text-gray-400"></p>
                </div>
            </div>
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

        /**
         * Chart.js llega como módulo ES asíncrono (bloque de assets de Livewire): con carga diferida
         * (lazy) y red lenta el componente puede intentar dibujar antes de que
         * exista window.Chart. Se espera al evento `chartjs:ready` y, si el
         * script aún no está en la página, se agrega (un módulo con la misma URL
         * se ejecuta una sola vez, así que no se duplica).
         */
        const chartJsUrl = @js(\Illuminate\Support\Facades\Vite::asset('resources/js/chart.js'));

        function whenChartReady(callback) {
            if (window.Chart) {
                callback();
                return;
            }

            window.addEventListener('chartjs:ready', () => callback(), { once: true });

            if (!document.querySelector(`script[type="module"][src="${chartJsUrl}"]`)) {
                const script = document.createElement('script');
                script.type = 'module';
                script.src = chartJsUrl;
                document.head.appendChild(script);
            }
        }

        // ── Textos legibles para tooltip y resumen ──────────────────────────

        function unitOf(label) {
            const match = /\(([^)]+)\)/.exec(label || '');
            return match ? match[1] : '';
        }

        function measureName(yLabelText) {
            return (yLabelText || '').replace(/\s*\([^)]*\)/, '') || 'Medición';
        }

        function formatDate(iso) {
            if (!iso) return '';
            const [y, m, d] = String(iso).split('-');
            return `${d}/${m}/${y}`;
        }

        function formatNumber(value) {
            return Number(value).toFixed(1);
        }

        function formatAge(months) {
            const total = Math.round(Number(months));
            const monthsText = `${total} ${total === 1 ? 'mes' : 'meses'}`;
            if (total < 24) return monthsText;

            const years = Math.floor(total / 12);
            const rest = total % 12;
            const yearsText = `${years} años`;

            return rest === 0
                ? `${monthsText} (${yearsText})`
                : `${monthsText} (${yearsText} y ${rest} ${rest === 1 ? 'mes' : 'meses'})`;
        }

        function isHeightAxis(xLabelText) {
            return (xLabelText || '').startsWith('Talla');
        }

        function xTitle(xLabelText, x) {
            return isHeightAxis(xLabelText) ? `Talla: ${formatNumber(x)} cm` : `Edad: ${formatAge(x)}`;
        }

        /** "a los 5 meses" / "a los 2 años y 2 meses (26 meses)". */
        function ageSentence(months) {
            const total = Math.round(Number(months));
            if (total < 24) return `a los ${total} ${total === 1 ? 'mes' : 'meses'}`;

            const years = Math.floor(total / 12);
            const rest = total % 12;
            const yearsText = rest === 0 ? `${years} años` : `${years} años y ${rest} ${rest === 1 ? 'mes' : 'meses'}`;

            return `a los ${yearsText} (${total} meses)`;
        }

        function patientSentence(point, xLabelText, yLabelText, mode, startLower = false) {
            const unit = unitOf(yLabelText);
            const verb = measureName(yLabelText).startsWith('Peso') ? 'pesaba' : 'medía';
            const when = isHeightAxis(xLabelText) ? `con ${formatNumber(point.x)} cm de talla` : ageSentence(point.x);
            const detail =
                mode === 'medico'
                    ? `Z-score ${point.z_score} (${point.category})`
                    : `percentil ~${zToPercentile(point.z_score)}`;

            return `${startLower ? 'el' : 'El'} ${formatDate(point.date)}, ${when}, ${verb} ${formatNumber(point.y)} ${unit} · ${detail}`;
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
            _pendingRender: null,

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

            /** Resumen fijo: la medición más reciente del paciente en esta boleta. */
            lastPointText() {
                const points = this._data?.patient_datapoints ?? [];
                if (points.length === 0) return '';

                const last = [...points].sort((a, b) => String(a.date).localeCompare(String(b.date))).at(-1);
                const count = points.length === 1 ? '1 medición' : `${points.length} mediciones`;

                return `Última medición: ${patientSentence(last, this._xL, this._yL, this._mode, true)} (${count} en la gráfica).`;
            },

            /** Qué significa cada línea, según la vista. */
            legendText() {
                const name = measureName(this._yL).toLowerCase();
                if (this._mode === 'medico') {
                    return `Líneas OMS de desvío estándar: la verde es la mediana; amarillas ±1 DS, naranjas ±2 DS y rojas ±3 DS. Cada punto es una medición del paciente. Pase el cursor o toque la gráfica para ver edad, medición, fecha y Z-score.`;
                }
                return `Línea verde: ${name} ideal para la edad (percentil 50). Líneas rojas punteadas: mínimo (P3) y máximo (P97) esperados; entre ellas está el rango normal. Cada punto es una medición del paciente. Pase el cursor o toque la gráfica para ver edad, medición y fecha.`;
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
                if (!window.Chart) {
                    // Se guarda el último pedido: si llegan varios antes de que
                    // cargue Chart.js, se dibuja solo el más reciente.
                    const pending = !this._pendingRender;
                    this._pendingRender = [data, xL, yL, mode, maxX];
                    if (pending) {
                        whenChartReady(() => {
                            const args = this._pendingRender;
                            this._pendingRender = null;
                            this.render(...args);
                        });
                    }
                    return;
                }

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

                // Datos para el tooltip: unidad, vista y puntos del paciente. La
                // tolerancia es medio paso del eje X (1 mes o 0,5 cm según boleta).
                const unit = unitOf(yL);
                const viewMode = this._mode;
                const patientPoints = data.patient_datapoints.map((p) => ({ ...p }));
                const step = data.labels.length > 1 ? Math.abs(data.labels[1] - data.labels[0]) : 1;
                const tolerance = step / 2;

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

                this.$el._chart = new window.Chart(canvas, {
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
                                // El modo "index" alinea por posición de dato: el punto del
                                // paciente se busca aparte por su edad/talla real (afterBody).
                                filter: (item) => item.dataset.type !== 'scatter',
                                callbacks: {
                                    title(items) {
                                        return items.length ? xTitle(xL, items[0].label) : '';
                                    },
                                    label(ctx) {
                                        return `${ctx.dataset.label}: ${formatNumber(ctx.parsed.y)} ${unit}`;
                                    },
                                    afterBody(items) {
                                        if (!items.length) return [];

                                        const x = Number(items[0].label);
                                        const matches = patientPoints.filter((p) => Math.abs(p.x - x) <= tolerance);

                                        if (matches.length === 0) return [];

                                        return [
                                            '',
                                            'Paciente:',
                                            ...matches.map((p) => patientSentence(p, xL, yL, viewMode)),
                                        ];
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
