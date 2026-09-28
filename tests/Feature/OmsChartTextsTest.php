<?php

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\WhoDataSeeder;
use Livewire\Livewire;

/*
 * La gráfica debe explicarse sola: edad legible en el tooltip ("Edad: 26 meses
 * (2 años y 2 meses)"), unidad en cada línea OMS, la medición del paciente con
 * su fecha y un resumen fijo debajo de la gráfica.
 */

beforeEach(function () {
    $this->seed(WhoDataSeeder::class);
    $this->patient = Patient::factory()->create([
        'gender' => 'F',
        'date_of_birth' => now()->subMonths(26)->toDateString(),
    ]);
});

it('muestra el bloque de resumen y leyenda debajo de la gráfica', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('patient-oms-chart', ['patientId' => $this->patient->id])
        ->assertSee('dusk="oms-chart-summary"', false)
        ->assertSee('lastPointText()', false)
        ->assertSee('legendText()', false)
        ->assertSee('Todavía no hay mediciones del paciente en esta boleta.');
});

it('el tooltip usa edad legible, unidades y la medición del paciente', function () {
    $component = Livewire::actingAs(User::factory()->create())
        ->test('patient-oms-chart', ['patientId' => $this->patient->id]);

    $scripts = collect($component->effects['scripts'] ?? [])->implode("\n");

    expect($scripts)
        ->toContain('function formatAge(months)')
        ->toContain('Edad: ${formatAge(x)}')
        ->toContain('${formatNumber(ctx.parsed.y)} ${unit}')
        ->toContain("'Paciente:'")
        ->toContain('Última medición:')
        ->toContain("verb = measureName(yLabelText).startsWith('Peso') ? 'pesaba' : 'medía'");
});

it('el cambio a talla envía la boleta y la etiqueta de talla', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('patient-oms-chart', ['patientId' => $this->patient->id])
        ->set('filterTipo', 'talla')
        ->assertDispatched('oms-chart-data', fn ($name, $params) => $params['data']['grafica']['tipo_grafica'] === 'talla_edad'
            && $params['yLabel'] === 'Talla (cm)'
            && $params['xLabel'] === 'Edad (meses)');
});
