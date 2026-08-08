<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;

test('el historial de recetas resume por consulta con diagnóstico y conteo de medicamentos', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
        'consultation_date' => '2026-08-06 09:00:00',
    ]);

    $prescription = Prescription::factory()->create([
        'consultation_id' => $consultation->id,
        'reason' => 'Astuto enfermedad',
    ]);
    PrescriptionItem::factory()->count(5)->create(['prescription_id' => $prescription->id]);

    $this->actingAs($user)
        ->get(route('pacientes.recetas', $patient))
        ->assertOk()
        ->assertSeeText('06/08/2026')
        ->assertSeeText('Astuto enfermedad')
        ->assertSeeText('5 medicamentos');
});

test('el historial de recetas no lista el detalle de cada medicamento', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $prescription = Prescription::factory()->create([
        'consultation_id' => $consultation->id,
        'reason' => 'Fiebre por dengue',
    ]);
    PrescriptionItem::factory()->create([
        'prescription_id' => $prescription->id,
        'medication_name' => 'Amoxicilina 250mg',
        'dose' => '1 cucharada',
        'frequency' => 'cada 8 hrs',
        'duration' => '7 dias',
        'instructions' => 'Tomar después de comer',
    ]);

    $this->actingAs($user)
        ->get(route('pacientes.recetas', $patient))
        ->assertOk()
        ->assertSeeText('Fiebre por dengue')
        ->assertSeeText('1 medicamento')
        ->assertDontSeeText('Amoxicilina 250mg')
        ->assertDontSeeText('1 cucharada')
        ->assertDontSeeText('Tomar después de comer');
});

test('el historial de recetas agrupa varias recetas de una misma consulta', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $primera = Prescription::factory()->create([
        'consultation_id' => $consultation->id,
        'reason' => 'Dengue sin signos de alarma',
    ]);
    PrescriptionItem::factory()->count(3)->create(['prescription_id' => $primera->id]);

    $segunda = Prescription::factory()->create([
        'consultation_id' => $consultation->id,
        'reason' => 'Suplementación vitamínica',
    ]);
    PrescriptionItem::factory()->count(2)->create(['prescription_id' => $segunda->id]);

    $this->actingAs($user)
        ->get(route('pacientes.recetas', $patient))
        ->assertOk()
        ->assertSeeText('Dengue sin signos de alarma')
        ->assertSeeText('3 medicamentos')
        ->assertSeeText('Suplementación vitamínica')
        ->assertSeeText('2 medicamentos');
});

test('el historial de recetas muestra el enlace al PDF de cada receta', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $prescription = Prescription::factory()->create(['consultation_id' => $consultation->id]);
    PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);

    $this->actingAs($user)
        ->get(route('pacientes.recetas', $patient))
        ->assertOk()
        ->assertSee(route('consultas.pdf.recetas.single', [$consultation, $prescription]))
        ->assertSee('dusk="prescription-pdf-'.$prescription->id.'"', false);
});

test('el historial de recetas pagina y no muestra una página interminable', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();

    // Paciente recurrente con muchas recetas (como en la obs de la clienta)
    foreach (range(1, 20) as $i) {
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'finalized',
            'consultation_date' => now()->subDays(20 - $i),
        ]);
        $prescription = Prescription::factory()->create([
            'consultation_id' => $consultation->id,
            'reason' => 'Diagnóstico '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
        ]);
        PrescriptionItem::factory()->create(['prescription_id' => $prescription->id]);
    }

    // Primera página: las 15 recetas más recientes, las 5 más antiguas quedan fuera
    $this->actingAs($user)
        ->get(route('pacientes.recetas', $patient))
        ->assertOk()
        ->assertSeeText('Diagnóstico 20')
        ->assertSeeText('Diagnóstico 06')
        ->assertDontSeeText('Diagnóstico 05')
        ->assertDontSeeText('Diagnóstico 01');

    // Página 2: las recetas más antiguas
    $this->actingAs($user)
        ->get(route('pacientes.recetas', [$patient, 'page' => 2]))
        ->assertOk()
        ->assertSeeText('Diagnóstico 05')
        ->assertSeeText('Diagnóstico 01')
        ->assertDontSeeText('Diagnóstico 20');
});

test('la sección de historial de recetas del perfil resume con diagnóstico y conteo', function (): void {
    $doctor = Doctor::factory()->create();
    $user = User::factory()->create(['doctor_id' => $doctor->id]);
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'status' => 'finalized',
    ]);

    $prescription = Prescription::factory()->create([
        'consultation_id' => $consultation->id,
        'reason' => 'IRAA con broncoespasmo leve',
    ]);
    PrescriptionItem::factory()->count(4)->create(['prescription_id' => $prescription->id]);

    $this->actingAs($user)
        ->get(route('pacientes.show', $patient))
        ->assertOk()
        ->assertSeeText('IRAA con broncoespasmo leve')
        ->assertSeeText('4 medicamentos')
        ->assertDontSeeText('dose');
});
