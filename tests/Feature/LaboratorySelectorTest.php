<?php

use App\Models\Consultation;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryExamParameter;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('al elegir un examen los parámetros inician sin seleccionar', function () {
    $exam = LaboratoryExam::factory()->create(['name' => 'Hemograma']);
    LaboratoryExamParameter::query()->create(['exam_id' => $exam->id, 'name' => 'Leucocitos', 'sort_order' => 0]);
    LaboratoryExamParameter::query()->create(['exam_id' => $exam->id, 'name' => 'Plaquetas', 'sort_order' => 1]);

    $consultation = Consultation::factory()->create(['status' => 'draft']);

    Livewire::test('consultation-laboratory', ['consultationId' => $consultation->id])
        ->call('selectExam', $exam->id)
        ->assertSet('selectorParameters', [
            ['name' => 'Leucocitos', 'checked' => false],
            ['name' => 'Plaquetas', 'checked' => false],
        ]);
});

test('setAllParamsChecked permite marcar y desmarcar todos los parámetros', function () {
    $exam = LaboratoryExam::factory()->create(['name' => 'Hemograma']);
    LaboratoryExamParameter::query()->create(['exam_id' => $exam->id, 'name' => 'Leucocitos', 'sort_order' => 0]);
    LaboratoryExamParameter::query()->create(['exam_id' => $exam->id, 'name' => 'Plaquetas', 'sort_order' => 1]);

    $consultation = Consultation::factory()->create(['status' => 'draft']);

    Livewire::test('consultation-laboratory', ['consultationId' => $consultation->id])
        ->call('selectExam', $exam->id)
        ->call('setAllParamsChecked', true)
        ->assertSet('selectorParameters.0.checked', true)
        ->assertSet('selectorParameters.1.checked', true)
        ->call('setAllParamsChecked', false)
        ->assertSet('selectorParameters.0.checked', false)
        ->assertSet('selectorParameters.1.checked', false);
});
