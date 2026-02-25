<?php

namespace Tests\Browser;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\LaboratoryTemplate;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;
use Tests\DuskTestCase;

/**
 * Tests de navegador real con Brave/Chrome (Laravel Dusk).
 *
 * Para correr estos tests, simplemente ejecuta:
 *   php artisan dusk
 *
 * El servidor se arranca automáticamente con --env=dusk.local (usa vitaltrack_dusk).
 * Si ya tienes un servidor corriendo en :8000, asegúrate de que use --env=dusk.local:
 *   php artisan serve --env=dusk.local
 */
class ClinicalWorkflowTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Limpia las cookies del browser antes de cada test para evitar
     * conflictos de sesión entre tests (el browser se reutiliza).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
        });
    }

    // =========================================================================
    // HELPER: Login reutilizable
    // =========================================================================

    private function loginAs(Browser $browser, string $email, string $password = 'password'): void
    {
        $browser
            ->visit('/login')
            ->waitFor('input[name="email"]', 10)
            ->clear('email')
            ->type('email', $email)
            ->clear('password')
            ->type('password', $password)
            ->click('[data-test="login-button"]')
            ->waitForLocation('/dashboard', 15);
    }

    // =========================================================================
    // TEST 1: LOGIN DESDE EL FORMULARIO
    // =========================================================================

    /**
     * El usuario puede iniciar sesión con credenciales válidas.
     */
    public function test_01_usuario_puede_hacer_login(): void
    {
        User::factory()->create([
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'doctor@test.com');
            $browser->assertPathIs('/dashboard');
        });
    }

    // =========================================================================
    // TEST 2: VER PERFIL DE DOCTOR
    // =========================================================================

    /**
     * El doctor puede ver su perfil profesional en /settings/professional.
     * flux:input con wire:model="full_name" renderiza <input name="full_name">.
     */
    public function test_02_doctor_puede_ver_su_perfil_profesional(): void
    {
        $doctor = Doctor::factory()->create([
            'full_name' => 'Dr. Nuevo Test',
            'specialty' => 'Pediatría',
            'license_number' => 'MP-TEST01',
        ]);

        // La ruta settings/professional requiere middleware('role:Doctor')
        $doctorRole = Role::firstOrCreate(['name' => 'Doctor', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'name' => 'Dr. Nuevo Test',
            'email' => 'drtest@clinica.com',
            'password' => bcrypt('password'),
            'doctor_id' => $doctor->id,
        ]);
        $user->assignRole($doctorRole);

        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'drtest@clinica.com');

            $browser
                ->visit('/settings/professional')
                ->waitFor('input[name="full_name"]', 10)
                ->assertInputValue('input[name="full_name"]', 'Dr. Nuevo Test');
        });

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'full_name' => 'Dr. Nuevo Test',
        ]);
    }

    // =========================================================================
    // TEST 3: CREAR PLANTILLA DE RECETA
    // =========================================================================

    /**
     * El doctor puede crear una plantilla de receta desde el frontend:
     * login → navega a /templates/prescriptions → abre modal → llena form → guarda.
     */
    public function test_03_puede_crear_plantilla_de_receta(): void
    {
        $doctor = Doctor::factory()->create([
            'full_name' => 'Dra. Ana García',
            'specialty' => 'Pediatría',
            'license_number' => 'MP-10001',
        ]);

        User::factory()->create([
            'email' => 'ana@clinica.com',
            'password' => bcrypt('password'),
            'doctor_id' => $doctor->id,
        ]);

        $medication = Medication::factory()->create([
            'name' => 'Amoxicilina',
            'concentration' => '500mg',
        ]);

        $this->browse(function (Browser $browser) use ($medication) {
            // ── 1. Login ──────────────────────────────────────────────────────
            $this->loginAs($browser, 'ana@clinica.com');

            // ── 2. Navegar a Plantillas de Receta ─────────────────────────────
            $browser
                ->visit('/templates/prescriptions')
                ->waitFor('@btn-nueva-plantilla', 10)
                ->assertSee('Plantillas de Receta');

            // ── 3. Abrir modal "Nueva Plantilla" ──────────────────────────────
            $browser
                ->click('@btn-nueva-plantilla')
                ->waitFor('dialog[open]', 10)
                ->assertSee('Nueva Plantilla');

            // ── 4. Escribir el nombre de la plantilla ─────────────────────────
            // flux:input wire:model="name" → renderiza <input wire:model="name">
            // type() con selector CSS funciona como tercer fallback en resolveForTyping()
            $browser->type('input[wire\\:model="name"]', 'Faringitis Aguda');

            // ── 5. Agregar el primer ítem ─────────────────────────────────────
            $browser
                ->click('@btn-agregar-item')
                ->waitFor('@select-medicamento-0', 10);

            // ── 6. Seleccionar medicamento y llenar campos ────────────────────
            // Dusk type() con selector CSS: resolveForTyping usa el 3er fallback como CSS
            $browser
                ->select('@select-medicamento-0', $medication->id)
                ->type('input[wire\\:model="items.0.dose"]', '500mg')
                ->type('input[wire\\:model="items.0.frequency"]', 'cada 8 horas')
                ->type('input[wire\\:model="items.0.duration"]', '7 días');

            // ── 7. Guardar ────────────────────────────────────────────────────
            $browser
                ->click('@btn-guardar-plantilla')
                ->waitUntilMissing('dialog[open]', 15)
                ->assertSee('Faringitis Aguda');
        });

        $this->assertDatabaseHas('prescription_templates', [
            'name' => 'Faringitis Aguda',
            'doctor_id' => $doctor->id,
        ]);
    }

    // =========================================================================
    // TEST 4: CREAR PLANTILLA DE LABORATORIO
    // =========================================================================

    /**
     * El doctor puede crear una plantilla de laboratorio desde el frontend:
     * login → navega a /templates/laboratories → abre modal → agrega examen → guarda.
     */
    public function test_04_puede_crear_plantilla_de_laboratorio(): void
    {
        $doctor = Doctor::factory()->create([
            'full_name' => 'Dr. Carlos Ruiz',
            'license_number' => 'MP-20002',
        ]);

        User::factory()->create([
            'email' => 'carlos@clinica.com',
            'password' => bcrypt('password'),
            'doctor_id' => $doctor->id,
        ]);

        $category = LaboratoryCategory::factory()->create(['name' => 'Hematología']);
        $exam = LaboratoryExam::factory()->create([
            'category_id' => $category->id,
            'name' => 'Hemograma Completo',
        ]);

        $this->browse(function (Browser $browser) use ($exam) {
            // ── 1. Login ──────────────────────────────────────────────────────
            $this->loginAs($browser, 'carlos@clinica.com');

            // ── 2. Navegar a Plantillas de Laboratorio ────────────────────────
            $browser
                ->visit('/templates/laboratories')
                ->waitFor('@btn-nueva-plantilla-lab', 10)
                ->assertSee('Plantillas de Laboratorio');

            // ── 3. Abrir modal ────────────────────────────────────────────────
            $browser
                ->click('@btn-nueva-plantilla-lab')
                ->waitFor('dialog[open]', 10);

            // ── 4. Escribir nombre ────────────────────────────────────────────
            $browser->type('input[wire\\:model="name"]', 'Perfil Infeccioso');

            // ── 5. Agregar examen ─────────────────────────────────────────────
            $browser
                ->click('@btn-agregar-examen')
                ->waitFor('@select-examen-0', 10);

            // ── 6. Seleccionar examen y llenar indicaciones ───────────────────
            $browser
                ->select('@select-examen-0', $exam->id)
                ->type('input[wire\\:model="items.0.indications"]', 'Ayuno de 8 horas');

            // ── 7. Guardar ────────────────────────────────────────────────────
            $browser
                ->click('@btn-guardar-plantilla-lab')
                ->waitUntilMissing('dialog[open]', 15)
                ->assertSee('Perfil Infeccioso');
        });

        $this->assertDatabaseHas('laboratory_templates', [
            'name' => 'Perfil Infeccioso',
            'doctor_id' => $doctor->id,
        ]);
    }

    // =========================================================================
    // TEST 5: CREAR PACIENTE
    // =========================================================================

    /**
     * El doctor puede registrar un nuevo paciente desde el formulario.
     * Botón submit: "Crear Paciente" (no "Guardar Paciente").
     */
    public function test_05_puede_crear_paciente(): void
    {
        $doctor = Doctor::factory()->create(['license_number' => 'MP-30003']);
        User::factory()->create([
            'email' => 'medico@clinica.com',
            'password' => bcrypt('password'),
            'doctor_id' => $doctor->id,
        ]);

        $this->browse(function (Browser $browser) {
            // ── 1. Login ──────────────────────────────────────────────────────
            $this->loginAs($browser, 'medico@clinica.com');

            // ── 2. Navegar al formulario de nuevo paciente ────────────────────
            $browser
                ->visit('/pacientes/create')
                ->waitForText('Nuevo Paciente', 10);

            // ── 3. Llenar y enviar ────────────────────────────────────────────
            $browser
                ->type('full_name', 'Juan Pérez López')
                ->type('date_of_birth', '2020-05-15')
                ->select('gender', 'M')
                ->press('Crear Paciente')
                ->waitForText('Juan Pérez López', 15);
        });

        $this->assertDatabaseHas('patients', [
            'full_name' => 'Juan Pérez López',
            'gender' => 'M',
        ]);
    }

    // =========================================================================
    // TEST 6: CREAR CONSULTA
    // =========================================================================

    /**
     * El doctor puede crear una consulta para un paciente desde el frontend.
     * consultation_date es datetime-local → formato Y-m-d\TH:i.
     */
    public function test_06_puede_crear_consulta(): void
    {
        $doctor = Doctor::factory()->create([
            'full_name' => 'Dra. María Torres',
            'license_number' => 'MP-40004',
        ]);
        User::factory()->create([
            'email' => 'maria@clinica.com',
            'password' => bcrypt('password'),
            'doctor_id' => $doctor->id,
        ]);
        $patient = Patient::factory()->create([
            'full_name' => 'Sofía Vargas',
        ]);

        $this->browse(function (Browser $browser) use ($doctor, $patient) {
            // ── 1. Login ──────────────────────────────────────────────────────
            $this->loginAs($browser, 'maria@clinica.com');

            // ── 2. Navegar al formulario de nueva consulta ────────────────────
            $browser
                ->visit('/consultas/create')
                ->waitForText('Nueva Consulta', 10);

            // ── 3. Llenar y enviar ────────────────────────────────────────────
            $browser
                ->select('patient_id', $patient->id)
                ->select('doctor_id', $doctor->id)
                ->select('type', 'digital')
                ->select('status', 'draft')
                ->type('consultation_date', now()->format('Y-m-d\TH:i'))
                ->press('Guardar Consulta')
                ->waitForText('Sofía Vargas', 15);
        });

        $this->assertDatabaseHas('consultations', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'type' => 'digital',
        ]);
    }

    // =========================================================================
    // TEST 7: FLUJO CLÍNICO COMPLETO EN UNA SESIÓN
    // =========================================================================

    /**
     * Flujo completo: un usuario hace login y en UNA sesión crea:
     *   1. Plantilla de receta
     *   2. Plantilla de laboratorio
     *   3. Paciente
     *   4. Consulta para ese paciente
     */
    public function test_07_flujo_clinico_completo(): void
    {
        $doctor = Doctor::factory()->create([
            'full_name' => 'Dr. Pedro Salinas',
            'specialty' => 'Pediatría General',
            'license_number' => 'MP-50005',
        ]);
        User::factory()->create([
            'email' => 'pedro@clinica.com',
            'password' => bcrypt('password'),
            'doctor_id' => $doctor->id,
        ]);

        $medication = Medication::factory()->create(['name' => 'Ibuprofeno', 'concentration' => '200mg/5ml']);
        $category = LaboratoryCategory::factory()->create(['name' => 'Bioquímica']);
        $exam = LaboratoryExam::factory()->create([
            'category_id' => $category->id,
            'name' => 'Glucosa en ayunas',
        ]);

        $this->browse(function (Browser $browser) use ($doctor, $medication, $exam) {
            // ═══════════════════════════════════════════════════════════════
            // PASO 1: Login
            // ═══════════════════════════════════════════════════════════════
            $this->loginAs($browser, 'pedro@clinica.com');
            $browser->assertPathIs('/dashboard');

            // ═══════════════════════════════════════════════════════════════
            // PASO 2: Crear plantilla de receta
            // ═══════════════════════════════════════════════════════════════
            $browser
                ->visit('/templates/prescriptions')
                ->waitFor('@btn-nueva-plantilla', 10)
                ->click('@btn-nueva-plantilla')
                ->waitFor('dialog[open]', 10)
                ->type('input[wire\\:model="name"]', 'Fiebre y Dolor')
                ->click('@btn-agregar-item')
                ->waitFor('@select-medicamento-0', 10)
                ->select('@select-medicamento-0', $medication->id)
                ->type('input[wire\\:model="items.0.dose"]', '200mg/5ml')
                ->type('input[wire\\:model="items.0.frequency"]', 'cada 6 horas')
                ->type('input[wire\\:model="items.0.duration"]', '3 días')
                ->click('@btn-guardar-plantilla')
                ->waitUntilMissing('dialog[open]', 15)
                ->assertSee('Fiebre y Dolor');

            // ═══════════════════════════════════════════════════════════════
            // PASO 3: Crear plantilla de laboratorio
            // ═══════════════════════════════════════════════════════════════
            $browser
                ->visit('/templates/laboratories')
                ->waitFor('@btn-nueva-plantilla-lab', 10)
                ->click('@btn-nueva-plantilla-lab')
                ->waitFor('dialog[open]', 10)
                ->type('input[wire\\:model="name"]', 'Control Metabólico')
                ->click('@btn-agregar-examen')
                ->waitFor('@select-examen-0', 10)
                ->select('@select-examen-0', $exam->id)
                ->type('input[wire\\:model="items.0.indications"]', 'Ayuno de 8 horas')
                ->click('@btn-guardar-plantilla-lab')
                ->waitUntilMissing('dialog[open]', 15)
                ->assertSee('Control Metabólico');

            // ═══════════════════════════════════════════════════════════════
            // PASO 4: Crear paciente
            // ═══════════════════════════════════════════════════════════════
            $browser
                ->visit('/pacientes/create')
                ->waitForText('Nuevo Paciente', 10)
                ->type('full_name', 'Valentina Cruz')
                ->type('date_of_birth', '2021-03-10')
                ->select('gender', 'F')
                ->press('Crear Paciente')
                ->waitForText('Valentina Cruz', 15);

            // ═══════════════════════════════════════════════════════════════
            // PASO 5: Crear consulta para el paciente
            // ═══════════════════════════════════════════════════════════════
            $patient = Patient::where('full_name', 'Valentina Cruz')->firstOrFail();

            $browser
                ->visit('/consultas/create')
                ->waitForText('Nueva Consulta', 10)
                ->select('patient_id', $patient->id)
                ->select('doctor_id', $doctor->id)
                ->select('type', 'digital')
                ->select('status', 'draft')
                ->type('consultation_date', now()->format('Y-m-d\TH:i'))
                ->press('Guardar Consulta')
                ->waitForText('Valentina Cruz', 15);
        });

        // Verificaciones finales en base de datos
        $this->assertDatabaseHas('prescription_templates', ['name' => 'Fiebre y Dolor']);
        $this->assertDatabaseHas('laboratory_templates', ['name' => 'Control Metabólico']);
        $this->assertDatabaseHas('patients', ['full_name' => 'Valentina Cruz']);
        $this->assertDatabaseHas('consultations', [
            'doctor_id' => $doctor->id,
            'type' => 'digital',
        ]);
    }

    // =========================================================================
    // TEST 8: CREAR SOLICITUD DE LABORATORIO DESDE CONSULTA
    // =========================================================================

    /**
     * El doctor puede crear una solicitud de laboratorio desde la vista de consulta:
     * - Guarda la solicitud (observaciones)
     * - Agrega un examen al snapshot
     */
    public function test_08_puede_crear_solicitud_de_laboratorio(): void
    {
        $doctor = Doctor::factory()->create([
            'full_name' => 'Dra. Lucía Mendez',
            'license_number' => 'MP-60006',
        ]);
        User::factory()->create([
            'email' => 'lucia@clinica.com',
            'password' => bcrypt('password'),
            'doctor_id' => $doctor->id,
        ]);
        $patient = Patient::factory()->create(['full_name' => 'Emilio Castillo']);
        $labTemplate = LaboratoryTemplate::factory()->create([
            'doctor_id' => $doctor->id,
            'name' => 'Panel Básico',
        ]);
        $consultation = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => 'saved',
        ]);

        $this->browse(function (Browser $browser) use ($consultation, $labTemplate) {
            // ── 1. Login ──────────────────────────────────────────────────────
            $this->loginAs($browser, 'lucia@clinica.com');

            // ── 2. Ir a la consulta ───────────────────────────────────────────
            $browser
                ->visit('/consultas/'.$consultation->id)
                ->waitForText('Solicitud de Laboratorio', 10);

            // ── 3. Seleccionar plantilla y agregar observaciones ───────────────
            $browser
                ->select('name=source_template_id', $labTemplate->id)
                ->type('name=observations', 'Ayunas de 8 horas previo al examen.')
                ->press('Guardar Solicitud')
                ->waitForText('guardada exitosamente', 10);

            // ── 4. Agregar un examen al snapshot ──────────────────────────────
            $browser
                ->type('name=exam_name', 'Hemograma completo')
                ->type('name=indications', 'Sin restricciones')
                ->press('Agregar Examen')
                ->waitForText('guardado exitosamente', 10)
                ->assertSee('Hemograma completo');
        });

        $this->assertDatabaseHas('laboratory_requests', [
            'consultation_id' => $consultation->id,
            'source_template_id' => $labTemplate->id,
        ]);

        $this->assertDatabaseHas('laboratory_request_items', [
            'exam_name' => 'Hemograma completo',
        ]);
    }
}
