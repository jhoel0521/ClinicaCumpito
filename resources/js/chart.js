/**
 * Entry point dedicado para Chart.js.
 *
 * Se carga solo cuando el componente OMS chart lo necesita
 * (via @assets + @vite en el blade).  Vite lo empaqueta
 * desde node_modules — sin CDN externo.
 *
 * Como es un módulo ES, se ejecuta de forma asíncrona: el componente puede
 * inicializarse antes (sobre todo con carga diferida y red lenta). Por eso,
 * además de exponer window.Chart, avisa con el evento `chartjs:ready`.
 */
import Chart from 'chart.js/auto';

window.Chart = Chart;
window.dispatchEvent(new Event('chartjs:ready'));
