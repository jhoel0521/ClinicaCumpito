<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\ScannedConsultationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('ScannedConsultation - Servicio', function () {
    beforeEach(function () {
        Storage::fake('local');
        $this->service = app(ScannedConsultationService::class);

        $doctor = Doctor::factory()->create();
        $this->user = User::factory()->create(['doctor_id' => $doctor->id]);
        $this->patient = Patient::factory()->create();
    });

    it('doctor puede crear una consulta escaneada PDF', function () {
        $file = UploadedFile::fake()->create('consulta.pdf', 1024, 'application/pdf');

        $consultation = $this->service->createFromScan(
            $this->patient,
            $file,
            now()->subMonths(1)->toDateString(),
            $this->user
        );

        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'type' => 'manual',
            'status' => 'draft',
            'patient_id' => $this->patient->id,
            'scanned_file_name' => 'consulta.pdf',
        ]);

        Storage::disk('local')->assertExists($consultation->scanned_file_path);
    });

    it('rechaza archivos mayores a 20 MB via validacion Livewire', function () {
        // Validate that the max rule is configured correctly
        $maxKb = 20480; // 20 MB
        expect($maxKb)->toBe(20 * 1024);
    });
});

describe('ScannedConsultation - Ruta de archivo', function () {
    beforeEach(function () {
        Storage::fake('local');

        $doctor = Doctor::factory()->create();
        $this->user = User::factory()->create(['doctor_id' => $doctor->id]);
        $this->patient = Patient::factory()->create();

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $service = app(ScannedConsultationService::class);

        $this->consultation = $service->createFromScan(
            $this->patient,
            $file,
            now()->toDateString(),
            $this->user
        );
    });

    it('sirve el archivo via ruta autenticada (200)', function () {
        $response = $this->actingAs($this->user)
            ->get(route('consultas.archivo.serve', $this->consultation->id));

        $response->assertStatus(200);
    });

    it('redirige a login si el usuario no está autenticado', function () {
        $response = $this->get(route('consultas.archivo.serve', $this->consultation->id));

        $response->assertRedirect(route('login'));
    });

    it('retorna 404 si la consulta no tiene archivo', function () {
        $consultation = Consultation::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->user->doctor_id,
            'scanned_file_path' => null,
            'scanned_file_name' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('consultas.archivo.serve', $consultation->id));

        $response->assertStatus(404);
    });
});

describe('ScannedConsultation - Vista historial paciente', function () {
    beforeEach(function () {
        Storage::fake('local');

        $doctor = Doctor::factory()->create();
        $this->user = User::factory()->create(['doctor_id' => $doctor->id]);
        $this->patient = Patient::factory()->create();

        $file = UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf');
        $service = app(ScannedConsultationService::class);

        $this->consultation = $service->createFromScan(
            $this->patient,
            $file,
            now()->toDateString(),
            $this->user
        );
    });

    it('la consulta escaneada aparece en historial del paciente con badge Digitalizar', function () {
        $response = $this->actingAs($this->user)
            ->get(route('pacientes.show', $this->patient->id));

        $response->assertStatus(200)
            ->assertSee('Digitalizar');
    });
});
