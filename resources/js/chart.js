/**
 * Entry point dedicado para Chart.js.
 *
 * Se carga solo cuando el componente OMS chart lo necesita
 * (via @assets + @vite en el blade).  Vite lo empaqueta
 * desde node_modules — sin CDN externo.
 */
import Chart from 'chart.js/auto';

window.Chart = Chart;
