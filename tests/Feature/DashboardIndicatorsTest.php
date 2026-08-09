<?php

use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\User;

test('el dashboard muestra indicadores reales y laboratorios pendientes', function (): void {
    $user = User::factory()->create();
    $patient = Patient::factory()->create();
    $consulta = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'finalized',
    ]);
    $pending = LaboratoryRequest::factory()->create([
        'consultation_id' => $consulta->id,
        'status' => 'pending',
    ]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $pending->id]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();

    $content = $response->getContent();

    expect(substr_count($content, '>1<'))->toBeGreaterThanOrEqual(1)
        ->and(str_contains($content, 'Labs pendientes'))->toBeTrue()
        ->and(str_contains($content, 'LaboratoryRequest::where(\'status\', \'pending\')'))->toBeFalse();
});

test('los indicadores del dashboard enlazan a sus listados', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('pacientes.index'))
        ->assertSee(route('consultas.index'))
        ->assertSee(route('consultas.index', ['status' => 'finalized']))
        ->assertSee(route('consultas.index', ['labsFilter' => 'pending']));
});

test('el indicador de laboratorios pendientes refleja los registros reales', function (): void {
    $user = User::factory()->create();
    $patient = Patient::factory()->create();
    $consulta = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'status' => 'saved',
    ]);
    LaboratoryRequest::factory()->count(3)->create([
        'consultation_id' => $consulta->id,
        'status' => 'pending',
    ]);
    $received = LaboratoryRequest::factory()->create([
        'consultation_id' => $consulta->id,
        'status' => 'received',
    ]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $received->id]);

    $content = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    // Los 3 pendientes se cuentan; el recibido no
    expect(LaboratoryRequest::where('status', 'pending')->count())->toBe(3)
        ->and(str_contains($content, 'Labs pendientes'))->toBeTrue();
});
