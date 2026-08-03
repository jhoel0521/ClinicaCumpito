<?php

use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('eliminar una fila de la receta borra solo ese medicamento y no otro', function () {
    $consultation = Consultation::factory()->create(['status' => 'draft']);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);

    $itemA = PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'medication_name' => 'Amoxicilina',
    ]);
    $itemB = PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'medication_name' => 'Paracetamol',
    ]);
    $itemC = PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'medication_name' => 'Ibuprofeno',
    ]);

    // Borrar la fila del medio (Paracetamol)
    Livewire::test('consultation-prescription', ['consultationId' => $consultation->id])
        ->call('removeItem', $prescription->id, $itemB->id)
        ->assertSet('prescriptions.0.items', function (array $items) use ($itemA, $itemC) {
            $ids = array_column($items, 'id');

            return count($items) === 2
                && in_array($itemA->id, $ids, true)
                && in_array($itemC->id, $ids, true);
        });

    $this->assertDatabaseMissing('prescription_items', ['id' => $itemB->id]);
    $this->assertDatabaseHas('prescription_items', ['id' => $itemA->id]);
    $this->assertDatabaseHas('prescription_items', ['id' => $itemC->id]);
});

test('cada fila de medicamento tiene un wire:key estable por id de ítem', function () {
    $consultation = Consultation::factory()->create(['status' => 'draft']);
    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    $item = PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

    Livewire::test('consultation-prescription', ['consultationId' => $consultation->id])
        ->assertSeeHtml('wire:key="rx-item-'.$item->id.'-main"')
        ->assertSeeHtml('wire:key="rx-item-'.$item->id.'-instr"')
        ->assertSeeHtml('wire:key="prescription-'.$prescription->id.'"');
});
