<?php

use App\Contracts\ConsultationServiceContract;
use App\DTOs\ConsultationDTO;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryAttachment;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryItemResult;
use App\Models\LaboratoryRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * Flujo completo de un laboratorio de imagen (imagenología):
 *
 * 1. En la consulta 1 se solicita el examen (p. ej. Radiografía de tórax)
 *    y la consulta 1 se cierra (finalizada).
 * 2. En la consulta 2 (mismo paciente) la orden sigue pendiente y desde
 *    ahí se registran los resultados del estudio y se suben los anexos
 *    (informe PDF e imagen).
 * 3. Se valida que los resultados quedaron guardados, que el archivo PDF
 *    y la imagen existen físicamente en el storage, que el adjunto quedó
 *    asociado a la orden correcta y que la orden sigue vinculada a la
 *    consulta 1 y al paciente correctos.
 */
test('laboratorio de imagen: se pide en consulta 1, se cierra, y en consulta 2 se cargan resultados y anexo PDF que se guarda en disco', function (): void {
    Storage::fake('public');

    // ── Catálogo de imagenología ─────────────────────────────────────────────
    $categoria = LaboratoryCategory::factory()->create(['name' => 'Imagenología']);
    $examen = LaboratoryExam::factory()->create([
        'category_id' => $categoria->id,
        'name' => 'Radiografía de tórax',
    ]);

    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();

    $consulta1 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
        'consultation_date' => '2026-08-01 09:00:00',
    ]);
    $consulta2 = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
        'consultation_date' => '2026-09-01 09:00:00',
    ]);

    // ── Paso 1: la consulta 1 solicita el laboratorio de imagen ─────────────
    Livewire::actingAs($user)
        ->test('consultation-laboratory', ['consultationId' => $consulta1->id])
        ->call('selectCategory', $categoria->id)
        ->call('selectExam', $examen->id)
        ->call('submitNewLabOrder')
        ->assertHasNoErrors();

    $orden = LaboratoryRequest::query()->where('consultation_id', $consulta1->id)->sole();

    expect($orden->status)->toBe('pending')
        ->and($orden->items)->toHaveCount(1)
        ->and($orden->items->first()->exam_name)->toBe('Radiografía de tórax')
        ->and($orden->consultation->patient_id)->toBe($patient->id);

    // ── Paso 2: la consulta 1 se cierra (finalizada) ────────────────────────
    app(ConsultationServiceContract::class)->update(
        $consulta1->id,
        ConsultationDTO::fromArray([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'type' => 'digital',
            'status' => 'finalized',
            'consultation_date' => $consulta1->consultation_date->format('Y-m-d H:i:s'),
            'scanned_file_path' => null,
            'pending_transcription' => false,
        ]),
    );

    expect($consulta1->fresh()->status->value())->toBe('finalized');

    // ── Paso 3: en la consulta 2 la orden pendiente sigue visible ───────────
    $componente = Livewire::actingAs($user)
        ->test('consultation-laboratory', ['consultationId' => $consulta2->id])
        ->assertOk();

    $componente->assertSee('Radiografía de tórax');

    $itemId = $orden->items->first()->id;

    // ── Paso 4: se registran los resultados del estudio de imagen ───────────
    $componente
        ->set('newResults.'.$itemId.'.value', 'Sin hallazgos patológicos')
        ->set('newResults.'.$itemId.'.report', 'Tórax sin condensaciones ni lesiones focales.')
        ->call('saveAllResults', $orden->id)
        ->assertHasNoErrors();

    $resultado = LaboratoryItemResult::query()->sole();

    expect($resultado->value)->toBe('Sin hallazgos patológicos')
        ->and($resultado->report_text)->toBe('Tórax sin condensaciones ni lesiones focales.')
        ->and($resultado->laboratory_request_item_id)->toBe($itemId)
        ->and($resultado->consultation_id)->toBe($consulta2->id);

    // ── Paso 5: se sube el anexo PDF (informe radiológico) ──────────────────
    $informePdf = UploadedFile::fake()->create('informe-radiografia.pdf', 150, 'application/pdf');

    $componente
        ->call('openAttachment', $orden->id)
        ->set('newAttachmentFile', $informePdf)
        ->call('uploadAttachment')
        ->assertHasNoErrors();

    $adjunto = LaboratoryAttachment::query()->sole();

    expect($adjunto->laboratory_request_id)->toBe($orden->id)
        ->and($adjunto->laboratory_request_item_id)->toBeNull()
        ->and($adjunto->original_name)->toBe('informe-radiografia.pdf')
        ->and($adjunto->mime_type)->toBe('application/pdf')
        ->and($adjunto->isPdf())->toBeTrue()
        ->and(Storage::disk('public')->exists($adjunto->file_path))->toBeTrue()
        ->and($adjunto->file_path)->toStartWith('lab-attachments/'.$orden->id.'/');

    // ── Paso 6: se sube también la imagen del estudio ───────────────────────
    $imagen = UploadedFile::fake()->image('placa-axial.jpg', 640, 480);

    $componente
        ->call('openAttachment', $orden->id)
        ->set('newAttachmentFile', $imagen)
        ->call('uploadAttachment')
        ->assertHasNoErrors();

    $adjuntoImagen = LaboratoryAttachment::query()
        ->where('id', '!=', $adjunto->id)
        ->sole();

    expect($adjuntoImagen->original_name)->toBe('placa-axial.jpg')
        ->and($adjuntoImagen->mime_type)->toBe('image/jpeg')
        ->and($adjuntoImagen->isImage())->toBeTrue()
        ->and(Storage::disk('public')->exists($adjuntoImagen->file_path))->toBeTrue();

    // ── Paso 7: la orden sigue asociada a la consulta 1 y al paciente ───────
    $orden->refresh();

    expect($orden->consultation_id)->toBe($consulta1->id)
        ->and($orden->consultation->patient_id)->toBe($patient->id)
        ->and($orden->status)->toBe('pending');

    // ── Paso 8: el detalle del laboratorio muestra resultados y anexos ──────
    $this->actingAs($user)
        ->get(route('pacientes.laboratorios.show', [$patient, $orden]))
        ->assertOk()
        ->assertSeeText('Sin hallazgos patológicos')
        ->assertSeeText('informe-radiografia.pdf')
        ->assertSeeText('placa-axial.jpg')
        ->assertSeeText('Documentos Adjuntos');
    $archivosEnDisco = Storage::disk('public')->allFiles('lab-attachments/'.$orden->id);

    expect($archivosEnDisco)->toHaveCount(2);
});
