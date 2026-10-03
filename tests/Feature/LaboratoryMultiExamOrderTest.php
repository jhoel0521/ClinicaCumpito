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
use App\Models\User;
use App\Services\ClinicalDocumentService;
use Livewire\Livewire;

/**
 * Una orden de laboratorio puede llevar varios exámenes de distintas
 * categorías (p. ej. cuadro digestivo: hemograma + ecografía + copro),
 * y se puede imprimir completa, por categoría o por examen.
 *
 * Categoría y examen solo navegan; los checks están únicamente en la
 * columna de parámetros ("Examen completo" si el examen no tiene parámetros).
 */
beforeEach(function (): void {
    ClinicSetting::create(['name' => 'Clínica Cumpito Test']);
    $this->actingAs(User::factory()->admin()->create());

    $this->hematologia = LaboratoryCategory::factory()->create(['name' => 'Hematología']);
    $this->imagenologia = LaboratoryCategory::factory()->create(['name' => 'Imagenología']);
    $this->parasitologia = LaboratoryCategory::factory()->create(['name' => 'Parasitología']);

    $this->hemograma = LaboratoryExam::factory()->create(['category_id' => $this->hematologia->id, 'name' => 'Hemograma']);
    LaboratoryExamParameter::query()->create(['exam_id' => $this->hemograma->id, 'name' => 'Leucocitos', 'sort_order' => 0]);
    LaboratoryExamParameter::query()->create(['exam_id' => $this->hemograma->id, 'name' => 'Plaquetas', 'sort_order' => 1]);
    $this->protrombina = LaboratoryExam::factory()->create(['category_id' => $this->hematologia->id, 'name' => 'Tiempo de Protrombina']);
    $this->ecografia = LaboratoryExam::factory()->create(['category_id' => $this->imagenologia->id, 'name' => 'Ecografía abdominal']);
    $this->copro = LaboratoryExam::factory()->create(['category_id' => $this->parasitologia->id, 'name' => 'Coproparasitológico']);

    $this->consultation = Consultation::factory()->create([
        'patient_id' => Patient::factory()->create()->id,
        'doctor_id' => Doctor::factory()->create()->id,
        'status' => 'draft',
    ]);
});

test('una sola orden reúne exámenes de varias categorías', function (): void {
    Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->call('selectCategory', $this->hematologia->id)
        ->call('selectExam', $this->hemograma->id)
        ->set('selectorParameters.0.checked', true)
        ->call('selectExam', $this->protrombina->id)
        ->set('selectedExamWhole', true)
        ->call('selectCategory', $this->imagenologia->id)
        ->call('selectExam', $this->ecografia->id)
        ->set('selectedExamWhole', true)
        ->call('selectCategory', $this->parasitologia->id)
        ->call('selectExam', $this->copro->id)
        ->set('selectedExamWhole', true)
        ->set('newPresumptiveDiagnosis', 'Dolor abdominal en estudio')
        ->call('submitNewLabOrder')
        ->assertHasNoErrors()
        ->assertSet('pickedExams', []);

    $order = LaboratoryRequest::query()->where('consultation_id', $this->consultation->id)->sole();

    expect($order->presumptive_diagnosis)->toBe('Dolor abdominal en estudio')
        ->and($order->items->map(fn ($i) => [$i->exam_name, $i->parameter_name])->all())->toBe([
            ['Hemograma', 'Leucocitos'],
            ['Tiempo de Protrombina', null],
            ['Ecografía abdominal', null],
            ['Coproparasitológico', null],
        ])
        ->and($order->examsLabel())->toBe('Hemograma, Tiempo de Protrombina, Ecografía abdominal, Coproparasitológico');
});

test('abrir un examen sin marcar nada no lo agrega a la orden', function (): void {
    $component = Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->call('selectCategory', $this->hematologia->id)
        ->call('selectExam', $this->hemograma->id)
        ->call('selectExam', $this->protrombina->id)
        ->call('selectCategory', $this->imagenologia->id)
        ->call('selectExam', $this->ecografia->id)
        ->call('submitNewLabOrder');

    expect($component->get('pickedExams'))->toBe([])
        ->and(LaboratoryRequest::query()->where('consultation_id', $this->consultation->id)->exists())->toBeFalse();
});

test('desmarcar todos los parámetros saca el examen de la orden', function (): void {
    $component = Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->call('selectExam', $this->hemograma->id)
        ->set('selectorParameters.0.checked', true);

    expect($component->get('pickedExams'))->toHaveKey($this->hemograma->id);

    $component->set('selectorParameters.0.checked', false);

    expect($component->get('pickedExams'))->toBe([]);
});

test('los parámetros marcados no se pierden al cambiar de categoría o de examen', function (): void {
    Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->call('selectExam', $this->hemograma->id)
        ->set('selectorParameters.1.checked', true)
        ->call('selectCategory', $this->imagenologia->id)
        ->call('selectExam', $this->ecografia->id)
        ->assertSet("pickedExams.{$this->hemograma->id}.params.1.checked", true)
        ->call('selectExam', $this->hemograma->id)
        ->assertSet('selectedCategoryId', $this->hematologia->id)
        ->assertSet('selectorParameters.0.checked', false)
        ->assertSet('selectorParameters.1.checked', true);
});

test('el panel de la orden permite borrar exámenes y parámetros', function (): void {
    $component = Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->call('selectExam', $this->copro->id)
        ->set('selectedExamWhole', true)
        ->call('selectExam', $this->hemograma->id)
        ->call('setAllParamsChecked', true)
        ->call('removePickedParam', $this->hemograma->id, 0)
        ->assertSet('selectorParameters.0.checked', false)
        ->assertSet('selectorParameters.1.checked', true)
        ->call('removePickedExam', $this->hemograma->id)
        ->assertSet('selectorParameters.1.checked', false)
        ->call('removePickedExam', $this->copro->id);

    expect($component->get('pickedExams'))->toBe([]);
});

test('quitar el último parámetro de un examen no activo lo saca de la orden', function (): void {
    $component = Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->call('selectExam', $this->hemograma->id)
        ->set('selectorParameters.0.checked', true)
        ->call('selectExam', $this->ecografia->id)
        ->call('removePickedParam', $this->hemograma->id, 0);

    expect($component->get('pickedExams'))->toBe([]);
});

test('el buscador encuentra exámenes de cualquier categoría sin importar tildes', function (): void {
    $component = Livewire::test('consultation-laboratory', ['consultationId' => $this->consultation->id])
        ->set('examSearch', 'ecografia');

    expect($component->instance()->searchResults())->toBe([
        ['id' => $this->ecografia->id, 'name' => 'Ecografía abdominal', 'category' => 'Imagenología'],
    ]);

    $component->call('pickFromSearch', $this->ecografia->id)
        ->assertSet('examSearch', '')
        ->assertSet('selectedCategoryId', $this->imagenologia->id)
        ->assertSet('selectedExamId', $this->ecografia->id)
        ->assertSet('pickedExams', []);
});

test('la orden se imprime completa, por categoría o por examen', function (): void {
    $order = LaboratoryRequest::factory()->create(['consultation_id' => $this->consultation->id, 'status' => 'pending']);
    foreach (['Hemograma', 'Tiempo de Protrombina', 'Ecografía abdominal'] as $exam) {
        LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $order->id, 'exam_name' => $exam, 'parameter_name' => null]);
    }

    $documents = app(ClinicalDocumentService::class);
    $names = fn ($doc) => collect($doc->items)->pluck('exam_name')->all();

    expect($names($documents->ordenLaboratorio($order)))->toBe(['Hemograma', 'Tiempo de Protrombina', 'Ecografía abdominal'])
        ->and($names($documents->ordenLaboratorio($order, category: 'Hematología')))->toBe(['Hemograma', 'Tiempo de Protrombina'])
        ->and($names($documents->ordenLaboratorio($order, exam: 'Ecografía abdominal')))->toBe(['Ecografía abdominal'])
        ->and($documents->ordenLaboratorio($order, category: 'Uroanálisis')->errors)->not->toBe([]);

    $this->get(route('documentos.laboratorios.preview', ['laboratoryRequest' => $order, 'categoria' => 'Imagenología']))
        ->assertOk()
        ->assertSee('Ecografía abdominal')
        ->assertDontSee('Tiempo de Protrombina')
        ->assertSee(route('documentos.laboratorios.pdf', ['laboratoryRequest' => $order, 'categoria' => 'Imagenología']), false);
});
