import Chart from 'chart.js/auto';

// Conversión z_score → percentil aproximado (polinomio de Horner, normal CDF)
function zToPercentile(z) {
    const t = 1 / (1 + 0.2316419 * Math.abs(z));
    const d = 0.3989423 * Math.exp(-z * z / 2);
    const p = d * t * (0.319381530 + t * (-0.356563782 + t * (1.781477937 + t * (-1.821255978 + t * 1.330274429))));
    return z >= 0 ? Math.round((1 - p) * 100) : Math.round(p * 100);
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('omsChart', (initialData, xLabel, yLabel) => ({
        chart: null,
        mode: 'padres', // 'padres' | 'medico'  —  default = vista simplificada (como el mockup)

        init() {
            if (initialData) {
                this.$nextTick(() => this.render(initialData, xLabel, yLabel));
            }
        },

        setMode(m) {
            this.mode = m;
            if (this.chart) {
                this.render(this.chart.__oms_data, this.chart.__oms_xLabel, this.chart.__oms_yLabel);
            }
        },

        render(data, xL, yL) {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }

            const canvas = this.$el.querySelector('[dusk="chart-canvas"]');
            if (!canvas || !data) return;

            // Curvas de referencia según modo
            const referenceDs = this.mode === 'medico'
                ? data.reference_datasets.map(ds => ({
                    type: 'line',
                    label: ds.label,
                    data: ds.data,
                    borderColor: ds.color,
                    backgroundColor: 'transparent',
                    borderWidth: ds.label === 'Mediana' ? 2 : 1,
                    borderDash: (ds.label === '-3 DS' || ds.label === '+3 DS') ? [4, 4] : [],
                    pointRadius: 0,
                    tension: 0.3,
                }))
                : data.percentile_datasets.map(ds => ({
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

            // Puntos scatter del paciente
            const patientDs = {
                type: 'scatter',
                label: 'Paciente',
                data: data.patient_datapoints.map(p => ({
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

            const isMedico = this.mode === 'medico';

            this.chart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [...referenceDs, patientDs],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                                filter: item => item.text !== 'Paciente',
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
                                        if (isMedico) {
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
                            title: { display: true, text: xL },
                        },
                        y: {
                            grid: { color: '#f3f4f620' },
                            title: { display: true, text: yL },
                        },
                    },
                },
            });

            // Guardar referencia para re-render en cambio de modo (sin nuevo dispatch)
            this.chart.__oms_data   = data;
            this.chart.__oms_xLabel = xL;
            this.chart.__oms_yLabel = yL;
        },
    }));
});
