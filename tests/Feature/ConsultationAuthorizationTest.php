<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryAttachment;
use App\Models\LaboratoryRequest;
use App\Models\PatientVaccine;
use App\Models\Prescription;
use App\Models\SoapNote;
use App\Models\User;
use App\Models\Vaccine;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->doctor = Doctor::factory()->create();
    $this->owner = User::factory()->create(['doctor_id' => $this->doctor->id]);
    $this->otherDoctorUser = User::factory()->create(['doctor_id' => Doctor::factory()->create()->id]);
    $this->consultation = Consultation::factory()->draft()->create(['doctor_id' => $this->doctor->id]);
});

describe('Rutas HTTP de sub-recursos de la consulta', function () {
    it('impide que otro doctor edite la nota SOAP', function () {
        $this->actingAs($this->otherDoctorUser)
            ->put(route('consultas.soap-notes.update', $this->consultation), ['subjective' => 'intruso'])
            ->assertForbidden();

        expect(SoapNote::where('consultation_id', $this->consultation->id)->exists())->toBeFalse();
    });

    it('permite al doctor dueño guardar la nota SOAP', function () {
        $this->actingAs($this->owner)
            ->put(route('consultas.soap-notes.update', $this->consultation), ['subjective' => 'tos'])
            ->assertRedirect(route('consultas.show', $this->consultation->id));

        expect(SoapNote::where('consultation_id', $this->consultation->id)->value('subjective'))->toBe('tos');
    });

    it('no modifica la nota SOAP de una consulta finalizada', function () {
        $finalized = Consultation::factory()->finalized()->create(['doctor_id' => $this->doctor->id]);

        $this->actingAs($this->owner)
            ->from(route('consultas.show', $finalized->id))
            ->put(route('consultas.soap-notes.update', $finalized), ['subjective' => 'tarde'])
            ->assertSessionHasErrors('status');

        expect(SoapNote::where('consultation_id', $finalized->id)->exists())->toBeFalse();
    });
});

describe('Componentes Livewire de la consulta', function () {
    it('bloquea el cambio del id de consulta desde el navegador', function () {
        $other = Consultation::factory()->draft()->create(['doctor_id' => $this->doctor->id]);

        expect(fn () => Livewire::actingAs($this->owner)
            ->test('consultation-soap-note', ['consultationId' => $this->consultation->id])
            ->set('consultationId', $other->id))
            ->toThrow(CannotUpdateLockedPropertyException::class);
    });

    it('rechaza guardar el SOAP a un usuario sin permiso de edición', function () {
        Livewire::actingAs($this->otherDoctorUser)
            ->test('consultation-soap-note', ['consultationId' => $this->consultation->id])
            ->set('subjective', 'intruso')
            ->assertForbidden();

        expect(SoapNote::where('consultation_id', $this->consultation->id)->exists())->toBeFalse();
    });

    it('rechaza finalizar la consulta de otro doctor', function () {
        Livewire::actingAs($this->otherDoctorUser)
            ->test('consultation-header', ['consultationId' => $this->consultation->id])
            ->call('finalize')
            ->assertForbidden();

        expect($this->consultation->fresh()->status->isDraft())->toBeTrue();
    });

    it('no permite quitar una vacuna registrada en otra consulta', function () {
        $previous = Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->consultation->patient_id,
        ]);
        $applied = PatientVaccine::factory()->create([
            'patient_id' => $this->consultation->patient_id,
            'consultation_id' => $previous->id,
            'vaccine_id' => Vaccine::factory()->create()->id,
        ]);

        Livewire::actingAs($this->owner)
            ->test('consultation-vaccines', ['consultationId' => $this->consultation->id])
            ->call('removeApplied', $applied->id);

        expect(PatientVaccine::find($applied->id))->not->toBeNull();
    });
});

describe('Documentos y adjuntos clínicos', function () {
    it('exige sesión para ver la receta', function () {
        $prescription = Prescription::factory()->create(['consultation_id' => $this->consultation->id]);

        $this->get(route('documentos.recetas.preview', $prescription))->assertRedirect(route('login'));
    });

    it('sirve el adjunto de laboratorio solo con sesión y desde el disco privado', function () {
        Storage::fake('local');
        Storage::fake('public');

        $request = LaboratoryRequest::factory()->create(['consultation_id' => $this->consultation->id]);
        Storage::disk('local')->put('lab-attachments/'.$request->id.'/a.pdf', '%PDF-1.4');
        $attachment = LaboratoryAttachment::create([
            'laboratory_request_id' => $request->id,
            'file_path' => 'lab-attachments/'.$request->id.'/a.pdf',
            'original_name' => 'hemograma.pdf',
            'mime_type' => 'application/pdf',
            'sort_order' => 0,
        ]);

        expect($attachment->url())->not->toContain('/storage/');

        $this->get($attachment->url())->assertRedirect(route('login'));

        $response = $this->actingAs($this->owner)->get($attachment->url());
        $response->assertOk();
        expect($response->headers->get('Cache-Control'))->toContain('private');
    });

    it('sigue sirviendo adjuntos antiguos guardados en el disco público', function () {
        Storage::fake('local');
        Storage::fake('public');

        $request = LaboratoryRequest::factory()->create(['consultation_id' => $this->consultation->id]);
        Storage::disk('public')->put('lab-attachments/'.$request->id.'/viejo.jpg', 'jpg');
        $attachment = LaboratoryAttachment::create([
            'laboratory_request_id' => $request->id,
            'file_path' => 'lab-attachments/'.$request->id.'/viejo.jpg',
            'original_name' => 'viejo.jpg',
            'mime_type' => 'image/jpeg',
            'sort_order' => 0,
        ]);

        $this->actingAs($this->owner)->get($attachment->url())->assertOk();
    });
});
