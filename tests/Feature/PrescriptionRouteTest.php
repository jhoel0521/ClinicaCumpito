<?php

use App\DTOs\PrescriptionItemDTO;
use App\Models\ClinicSetting;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\PrescriptionItemService;

test('el formulario de receta incluye la vía de administración y la guarda', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    $item = PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'administration_route' => 'Oral',
    ]);

    $this->actingAs($user)
        ->get(route('consultas.show', $consultation))
        ->assertOk()
        ->assertSeeText('Vía');

    // El servicio persiste la vía al actualizar el ítem
    $service = new PrescriptionItemService;
    $updated = $service->update($item->id, PrescriptionItemDTO::fromArray([
        'medication_name' => 'Amoxicilina',
        'dose' => '5 ml',
        'administration_route' => 'Oral',
        'frequency' => 'Cada 8 horas',
        'duration' => '7 días',
    ]));

    expect($updated->administration_route)->toBe('Oral');
});

test('la receta impresa incluye la vía de administración', function (): void {
    ClinicSetting::create(['name' => 'Clínica Cumpito Test']);
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'medication_name' => 'Ibuprofeno',
        'administration_route' => 'Oral',
    ]);

    $html = view('documents.receta', [
        'doc' => app(\App\Services\ClinicalDocumentService::class)->receta($prescription),
    ])->render();

    expect($html)->toContain('Vía')
        ->and($html)->toContain('Oral');
});

test('la vía es opcional y se guarda como nula si no se indica', function (): void {
    $service = new PrescriptionItemService;
    $consultation = \App\Models\Consultation::factory()->create(['status' => 'saved']);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    $item = PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'administration_route' => null,
    ]);

    $updated = $service->update($item->id, PrescriptionItemDTO::fromArray([
        'medication_name' => 'Paracetamol',
        'dose' => '1 sobre',
        'frequency' => 'Cada 12 horas',
        'duration' => '3 días',
    ]));

    expect($updated->administration_route)->toBeNull();
});

test('la receta tiene un solo campo unificado de dosis y cantidad, sin cantidad separada', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'saved',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'dose' => '5 ml · 1 frasco 60 ml',
    ]);

    $response = $this->actingAs($user)
        ->get(route('consultas.show', $consultation))
        ->assertOk()
        ->assertSeeText('Dosis / Cantidad')
        ->assertDontSeeText('placeholder="Cantidad"')
        ->assertDontSeeText('>Cantidad</th>');

    expect($response->getContent())->not->toContain('quantity');
});

test('el PDF de receta muestra un solo campo unificado de dosis y cantidad', function (): void {
    ClinicSetting::create(['name' => 'Clínica Cumpito Test']);
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'medication_name' => 'Amoxicilina',
        'dose' => '5 ml · 1 frasco 60 ml',
    ]);

    $html = view('documents.receta', [
        'doc' => app(\App\Services\ClinicalDocumentService::class)->receta($prescription),
    ])->render();

    expect($html)->toContain('5 ml · 1 frasco 60 ml')
        ->and($html)->not->toContain('quantity')
        ->and($html)->not->toContain('>Cantidad<');
});
