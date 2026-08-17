<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryAttachment;
use App\Models\LaboratoryRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * El archivo de un estudio de laboratorio pertenece a UN paciente. Nada en
 * la pantalla de laboratorio de otra consulta -- aunque sea del mismo
 * médico -- puede leer el id de esa solicitud y manipular su archivo.
 */
test('no se puede eliminar el archivo de un laboratorio de otro paciente', function (): void {
    Storage::fake('public');

    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);

    $pacienteA = Patient::factory()->create();
    $consultaA = Consultation::factory()->create([
        'patient_id' => $pacienteA->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
    ]);
    $ordenA = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultaA->id,
        'status' => 'pending',
    ]);

    Storage::disk('public')->put('lab-attachments/'.$ordenA->id.'/estudio.pdf', 'contenido');
    $adjuntoA = LaboratoryAttachment::create([
        'laboratory_request_id' => $ordenA->id,
        'file_path' => 'lab-attachments/'.$ordenA->id.'/estudio.pdf',
        'original_name' => 'estudio-paciente-a.pdf',
        'mime_type' => 'application/pdf',
    ]);

    $pacienteB = Patient::factory()->create();
    $consultaB = Consultation::factory()->create([
        'patient_id' => $pacienteB->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
    ]);

    // Desde la pantalla de laboratorio de la consulta de B, se intenta
    // borrar el adjunto de la orden de A pasando su id directamente.
    Livewire::actingAs($user)
        ->test('consultation-laboratory', ['consultationId' => $consultaB->id])
        ->call('deleteAttachment', $ordenA->id, $adjuntoA->id);

    expect(LaboratoryAttachment::query()->whereKey($adjuntoA->id)->exists())->toBeTrue()
        ->and(Storage::disk('public')->exists($adjuntoA->file_path))->toBeTrue();
});

test('no se puede abrir el formulario de adjunto de un laboratorio de otro paciente', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);

    $pacienteA = Patient::factory()->create();
    $consultaA = Consultation::factory()->create([
        'patient_id' => $pacienteA->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
    ]);
    $ordenA = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultaA->id,
        'status' => 'pending',
    ]);

    $pacienteB = Patient::factory()->create();
    $consultaB = Consultation::factory()->create([
        'patient_id' => $pacienteB->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
    ]);

    expect(
        fn () => Livewire::actingAs($user)
            ->test('consultation-laboratory', ['consultationId' => $consultaB->id])
            ->call('openAttachment', $ordenA->id),
    )->toThrow(ModelNotFoundException::class);
});

test('no se puede reemplazar el archivo de un laboratorio de otro paciente vía uploadAttachment', function (): void {
    Storage::fake('public');

    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);

    $pacienteA = Patient::factory()->create();
    $consultaA = Consultation::factory()->create([
        'patient_id' => $pacienteA->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
    ]);
    $ordenA = LaboratoryRequest::factory()->create([
        'consultation_id' => $consultaA->id,
        'status' => 'pending',
    ]);

    $pacienteB = Patient::factory()->create();
    $consultaB = Consultation::factory()->create([
        'patient_id' => $pacienteB->id,
        'doctor_id' => $doctor->id,
        'status' => 'draft',
    ]);

    // attachingToRequestId se fija directo (bypassea openAttachment, que ya
    // está probado por separado) para aislar el guard dentro de uploadAttachment.
    Livewire::actingAs($user)
        ->test('consultation-laboratory', ['consultationId' => $consultaB->id])
        ->set('attachingToRequestId', $ordenA->id)
        ->set('newAttachmentFile', UploadedFile::fake()->create('intruso.pdf', 20, 'application/pdf'))
        ->call('uploadAttachment');

    expect(LaboratoryAttachment::query()->where('laboratory_request_id', $ordenA->id)->exists())->toBeFalse();
});
