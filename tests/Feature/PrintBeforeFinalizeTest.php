<?php

use App\Models\ClinicSetting;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryExamParameter;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\ClinicalDocumentService;
use Livewire\Livewire;

/**
 * Feedback de la doctora: quiere revisar el PDF de la receta y del
 * laboratorio ANTES de finalizar (si finaliza ya no puede corregir).
 *
 * Los links siguen en la columna izquierda (donde siempre estuvieron), pero
 * ahora son un componente Livewire que escucha `prescriptions-changed` y
 * `lab-requests-changed`: aparecen en cuanto hay contenido, sin finalizar ni F5.
 */
beforeEach(function (): void {
    ClinicSetting::create(['name' => 'Clínica Cumpito Test']);

    $doctor = Doctor::factory()->create();
    $this->user = User::factory()->create(['doctor_id' => $doctor->id]);
    $this->actingAs($this->user);

    $this->consultation = Consultation::factory()->create([
        'patient_id' => Patient::factory()->create()->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
    ]);
});

test('el link de imprimir receta de la columna izquierda aparece al escribir el primer medicamento, sin recargar', function (): void {
    $prescription = Prescription::factory()->create(['consultation_id' => $this->consultation->id, 'reason' => 'Dengue']);
    $item = PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'medication_name' => '',
        'dose' => '',
        'frequency' => '',
        'duration' => '',
    ]);

    $links = Livewire::test('consultation-print-links', ['consultationId' => $this->consultation->id, 'kind' => 'recetas'])
        ->assertSee('Dengue')
        ->assertSee('Agrega al menos un medicamento para imprimir')
        ->assertDontSee(route('documentos.recetas.preview', $prescription), false);

    // La tarjeta de receta guarda el medicamento y avisa a la columna izquierda.
    Livewire::test('consultation-prescription', ['consultationId' => $this->consultation->id])
        ->call('updateItemField', $prescription->id, $item->id, 'medication_name', 'Paracetamol')
        ->assertDispatched('prescriptions-changed');

    $links->dispatch('prescriptions-changed')
        ->assertSee(route('documentos.recetas.preview', $prescription), false)
        ->assertDontSee('Agrega al menos un medicamento para imprimir');
});

test('crear o borrar una receta avisa a la columna izquierda', function (): void {
    $component = Livewire::test('consultation-prescription', ['consultationId' => $this->consultation->id])
        ->set('newReason', 'Faringitis')
        ->call('createPrescription')
        ->assertDispatched('prescriptions-changed');

    $prescription = Prescription::query()->where('consultation_id', $this->consultation->id)->sole();

    Livewire::test('consultation-print-links', ['consultationId' => $this->consultation->id, 'kind' => 'recetas'])
        ->assertSee('Faringitis');

    $component->call('deletePrescription', $prescription->id)->assertDispatched('prescriptions-changed');
});

test('la receta de una consulta en borrador se puede ver antes de finalizar', function (): void {
    $prescription = Prescription::factory()->create(['consultation_id' => $this->consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id, 'medication_name' => 'Ibuprofeno']);

    $this->get(route('documentos.recetas.preview', $prescription))
        ->assertOk()
        ->assertSee('Ibuprofeno');

    $this->get(route('documentos.recetas.pdf', $prescription))->assertOk();

    expect($this->consultation->fresh()->status->value())->toBe('draft');
});

test('el PDF de la receta omite las filas que siguen vacías', function (): void {
    $prescription = Prescription::factory()->create(['consultation_id' => $this->consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id, 'medication_name' => 'Amoxicilina']);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id, 'medication_name' => '']);

    $doc = app(ClinicalDocumentService::class)->receta($prescription);

    expect($doc->errors)->toBe([])
        ->and(collect($doc->items)->pluck('medication_name')->all())->toBe(['Amoxicilina']);
});

test('una receta con solo filas vacías no se imprime', function (): void {
    $prescription = Prescription::factory()->create(['consultation_id' => $this->consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id, 'medication_name' => '']);

    expect(app(ClinicalDocumentService::class)->receta($prescription)->errors)->not->toBe([]);
});

test('la orden de laboratorio recién creada aparece en la columna izquierda con imprimir y ver detalle', function (): void {
    $hema = LaboratoryCategory::factory()->create(['name' => 'Hematología']);
    $imagen = LaboratoryCategory::factory()->create(['name' => 'Imagenología']);
    $hemograma = LaboratoryExam::factory()->create(['category_id' => $hema->id, 'name' => 'Hemograma']);
    LaboratoryExamParameter::query()->create(['exam_id' => $hemograma->id, 'name' => 'Leucocitos', 'sort_order' => 0]);
    $eco = LaboratoryExam::factory()->create(['category_id' => $imagen->id, 'name' => 'Ecografía']);
    LaboratoryExamParameter::query()->create(['exam_id' => $eco->id, 'name' => 'Abdominal', 'sort_order' => 0]);

    $links = Livewire::test('consultation-print-links', [
        'consultationId' => $this->consultation->id,
        'kind' => 'laboratorios',
        'patientId' => $this->consultation->patient_id,
    ])->assertDontSee('Solicitud #1');

    Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->call('selectExam', $hemograma->id)
        ->set('selectorParameters.0.checked', true)
        ->call('selectExam', $eco->id)
        ->set('selectorParameters.0.checked', true)
        ->call('submitNewLabOrder')
        ->assertDispatched('lab-requests-changed');

    $order = LaboratoryRequest::query()->where('consultation_id', $this->consultation->id)->sole();

    $links->dispatch('lab-requests-changed')
        ->assertSee('Solicitud #1')
        ->assertSee(route('documentos.laboratorios.preview', $order), false)
        ->assertSee(e(route('documentos.laboratorios.preview', ['laboratoryRequest' => $order, 'categoria' => 'Imagenología'])), false)
        ->assertSee(e(route('documentos.laboratorios.preview', ['laboratoryRequest' => $order, 'examen' => 'Hemograma'])), false)
        ->assertSee(route('pacientes.laboratorios.show', [$this->consultation->patient_id, $order]), false);

    $this->get(route('documentos.laboratorios.preview', $order))->assertOk()->assertSee('Hemograma');
});

test('los links de imprimir están en la columna izquierda y no duplicados en las tarjetas', function (): void {
    $prescription = Prescription::factory()->create(['consultation_id' => $this->consultation->id, 'reason' => 'Dengue']);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id, 'medication_name' => 'Paracetamol']);
    $lab = LaboratoryRequest::factory()->create(['consultation_id' => $this->consultation->id]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $lab->id]);

    $html = $this->get(route('consultas.show', $this->consultation))->assertOk()->getContent();

    expect(substr_count($html, 'href="'.route('documentos.recetas.preview', $prescription).'"'))->toBe(1)
        ->and(substr_count($html, 'href="'.route('documentos.laboratorios.preview', $lab).'"'))->toBe(1)
        ->and($html)->toContain('dusk="print-links-recetas"')
        ->and($html)->toContain('dusk="print-links-laboratorios"')
        ->and($html)->toContain('Solicitud #1');
});

test('la vista previa tiene solo descargar, imprimir y volver a la consulta', function (): void {
    $prescription = Prescription::factory()->create(['consultation_id' => $this->consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id, 'medication_name' => 'Paracetamol']);
    $lab = LaboratoryRequest::factory()->create(['consultation_id' => $this->consultation->id]);
    LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $lab->id]);

    $cases = [
        [route('documentos.recetas.preview', $prescription), '#receta'],
        [route('documentos.laboratorios.preview', $lab), '#laboratorio'],
    ];

    foreach ($cases as [$url, $anchor]) {
        $this->get($url)
            ->assertOk()
            ->assertSee('dusk="preview-download"', false)
            ->assertSee('dusk="preview-print"', false)
            ->assertSeeText('← Volver a la consulta')
            ->assertSee('href="'.route('consultas.show', $this->consultation).$anchor.'"', false)
            ->assertDontSeeText('Cerrar')
            ->assertDontSeeText('← Editar');
    }
});

test('el borrador del formulario de laboratorio se restaura tras un F5', function (): void {
    $category = LaboratoryCategory::factory()->create(['name' => 'Hematología']);
    $exam = LaboratoryExam::factory()->create(['category_id' => $category->id, 'name' => 'Hemograma']);
    LaboratoryExamParameter::query()->create(['exam_id' => $exam->id, 'name' => 'Leucocitos', 'sort_order' => 0]);
    LaboratoryExamParameter::query()->create(['exam_id' => $exam->id, 'name' => 'Plaquetas', 'sort_order' => 1]);

    $draft = [
        'picked' => [
            $exam->id => ['name' => 'Nombre alterado', 'category_id' => 'x', 'whole' => false, 'params' => [
                ['name' => 'Leucocitos', 'checked' => false],
                ['name' => 'Plaquetas', 'checked' => true],
            ]],
            'id-que-no-existe' => ['whole' => true, 'params' => []],
        ],
        'diagnosis' => 'Anemia en estudio',
        'observations' => 'Ayunas',
        'activeExamId' => $exam->id,
    ];

    Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->call('restoreDraft', $draft)
        ->assertSet('showNewForm', true)
        ->assertSet('newPresumptiveDiagnosis', 'Anemia en estudio')
        ->assertSet('newObservations', 'Ayunas')
        ->assertSet('selectedExamId', $exam->id)
        ->assertSet('selectorParameters.1.checked', true)
        ->assertSet('pickedExams', [
            $exam->id => [
                'name' => 'Hemograma',
                'category_id' => $category->id,
                'whole' => false,
                'params' => [
                    ['name' => 'Leucocitos', 'checked' => false],
                    ['name' => 'Plaquetas', 'checked' => true],
                ],
            ],
        ]);
});

test('el borrador no se restaura en una consulta finalizada', function (): void {
    $this->consultation->update(['status' => 'finalized']);

    Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->call('restoreDraft', ['diagnosis' => 'X'])
        ->assertSet('showNewForm', false)
        ->assertSet('newPresumptiveDiagnosis', '');
});
