<?php

use App\Models\Patient;
use App\Models\User;
use Livewire\Livewire;

/*
 * Regresión: con la gráfica en carga diferida, el módulo de Chart.js podía
 * llegar después del primer render ("Chart is not defined" en producción).
 */

it('la gráfica espera a que Chart.js esté listo antes de dibujar', function () {
    $patient = Patient::factory()->create(['gender' => 'F']);

    $component = Livewire::actingAs(User::factory()->create())
        ->test('patient-oms-chart', ['patientId' => $patient->id]);

    $scripts = collect($component->effects['scripts'] ?? [])->implode("\n");

    expect($scripts)->toContain('whenChartReady')
        ->toContain('chartjs:ready')
        ->toContain('new window.Chart(')
        // @js escapa las barras (http:\/\/...).
        ->toContain(str_replace('/', '\/', Illuminate\Support\Facades\Vite::asset('resources/js/chart.js')));
});

it('el entry de Chart.js avisa cuando quedó disponible', function () {
    $entry = file_get_contents(resource_path('js/chart.js'));

    expect($entry)->toContain('window.Chart = Chart')
        ->toContain("new Event('chartjs:ready')");
});
