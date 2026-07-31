<?php

namespace Tests\Browser;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use App\Models\Medication;
use App\Models\OmsCatalogoGrafica;
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

        $this->browse(function (Browser $browser) use ($patient) {
            // ── 1. Login ──────────────────────────────────────────────────────
            $this->loginAs($browser, 'maria@clinica.com');

            // ── 2. Iniciar la consulta desde el perfil del paciente ───────────
            $browser
                ->visit('/pacientes/'.$patient->id)
                ->waitFor('@create-consultation', 10)
                ->click('@create-consultation')
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

        $this->browse(function (Browser $browser) use ($medication, $exam) {
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
                ->visit('/pacientes/'.$patient->id)
                ->waitFor('@create-consultation', 10)
                ->click('@create-consultation')
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
        $category = LaboratoryCategory::factory()->create(['name' => 'Hematología']);
        $exam = LaboratoryExam::factory()->create(['name' => 'Hemograma completo', 'category_id' => $category->id]);
        $consultation = Consultation::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => 'saved',
        ]);

        $this->browse(function (Browser $browser) use ($consultation, $category, $exam) {
            // ── 1. Login ──────────────────────────────────────────────────────
            $this->loginAs($browser, 'lucia@clinica.com');

            // ── 2. Ir a la consulta y abrir el formulario de lab ──────────────
            $browser
                ->visit('/consultas/'.$consultation->id)
                ->waitForText('Solicitudes de Laboratorio', 10);

            // ── 3. Abrir panel y seleccionar categoría/examen ─────────────────
            $browser->script("document.querySelector('[dusk=\"section-laboratory\"] button').click()");
            $browser
                ->waitForText('Categoría', 10)
                ->waitForText($category->name, 10);
            $browser->script("
                document.querySelectorAll('[dusk=\"section-laboratory\"] button')[1].click();
            ");
            $browser->waitForText($exam->name, 10);
            $browser->script("
                Array.from(document.querySelectorAll('[dusk=\"section-laboratory\"] button'))
                    .find(b => b.textContent.trim() === '{$exam->name}').click();
            ");
            $browser->pause(500);
            $browser->script("document.querySelector('button[wire\\\\:click=\"submitNewLabOrder\"]').click()");
            $browser->waitForText($exam->name, 10);
        });

        $this->assertDatabaseHas('laboratory_requests', [
            'consultation_id' => $consultation->id,
        ]);

        $this->assertDatabaseHas('laboratory_request_items', [
            'exam_name' => 'Hemograma completo',
        ]);
    }

    // =========================================================================
    // TEST 9: CREAR BOLETA OMS DESDE EL CATÁLOGO
    // =========================================================================

    /**
     * El admin puede crear una boleta OMS desde el frontend:
     * login → navega a /catalogs/oms-graficas → abre modal → llena form → guarda.
     */
    public function test_09_admin_puede_crear_boleta_oms(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($adminRole);

        $this->browse(function (Browser $browser) {
            // ── 1. Login ──────────────────────────────────────────────────────
            $this->loginAs($browser, 'admin@test.com');

            // ── 2. Navegar al catálogo de gráficas OMS ────────────────────────
            $browser
                ->visit('/catalogs/oms-graficas')
                ->waitFor('@btn-nueva-grafica', 10)
                ->assertSee('Gráficas OMS');

            // ── 3. Abrir modal ────────────────────────────────────────────────
            $browser->script("document.querySelector('[dusk=\"btn-nueva-grafica\"]').click()");
            $browser->waitFor('dialog[open]', 10);

            // ── 4. Llenar el formulario ───────────────────────────────────────
            $browser
                ->type('input[wire\\:model="nombre"]', 'Peso para Talla - Niños')
                ->type('input[wire\\:model="codigo"]', 'WHO_WT_LEN_MALE_0_24M')
                ->select('select[wire\\:model="tipoGrafica"]', 'peso_talla')
                ->type('input[wire\\:model="rangoEdad"]', '0-24 meses')
                ->select('select[wire\\:model="sexo"]', 'M');

            // ── 5. Guardar ────────────────────────────────────────────────────
            $browser->script("document.querySelector('[dusk=\"btn-guardar-grafica\"]').click()");
            $browser
                ->waitUntilMissing('dialog[open]', 15)
                ->assertSee('Peso para Talla - Niños');
        });

        $this->assertDatabaseHas('oms_catalogo_graficas', [
            'codigo' => 'WHO_WT_LEN_MALE_0_24M',
        ]);
    }

    // =========================================================================
    // TEST 10: AGREGAR DATO LMS A UNA BOLETA OMS
    // =========================================================================

    /**
     * El admin puede agregar un punto de datos LMS a una boleta OMS:
     * login → navega a /catalogs/oms-datos/{id} → abre modal → llena form → guarda.
     */
    public function test_10_admin_puede_agregar_dato_oms(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $admin = User::factory()->create([
            'email' => 'admin.datos@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($adminRole);

        $grafica = OmsCatalogoGrafica::factory()->create([
            'nombre' => 'Talla para Edad - Niños OMS',
            'codigo' => 'WHO_LEN_AGE_M_0_5Y_DUSK',
            'tipo_grafica' => 'talla_edad',
            'rango_edad' => '0-5 años',
            'sexo' => 'M',
        ]);

        $this->browse(function (Browser $browser) use ($grafica) {
            // ── 1. Login ──────────────────────────────────────────────────────
            $this->loginAs($browser, 'admin.datos@test.com');

            // ── 2. Navegar directamente a los datos de la boleta OMS ──────────
            $browser
                ->visit('/catalogs/oms-datos/'.$grafica->id)
                ->waitFor('@btn-nuevo-dato', 10)
                ->assertSee('Talla para Edad - Niños OMS');

            // ── 3. Abrir modal ────────────────────────────────────────────────
            $browser->script("document.querySelector('[dusk=\"btn-nuevo-dato\"]').click()");
            $browser->waitFor('dialog[open]', 10);

            // ── 4. Llenar el formulario (LMS obligatorios) ────────────────────
            $browser
                ->type('input[wire\\:model="xValue"]', '3')
                ->type('input[wire\\:model="lValue"]', '0.1738')
                ->type('input[wire\\:model="mValue"]', '6.3762')
                ->type('input[wire\\:model="sValue"]', '0.11727');

            // ── 5. Guardar ────────────────────────────────────────────────────
            $browser->script("document.querySelector('[dusk=\"btn-guardar-dato\"]').click()");
            $browser->waitUntilMissing('dialog[open]', 15);

            // ── 6. Verificar que el dato aparece en la tabla ──────────────────
            // x_value = 3 aparece como "3.0000" en la columna X de la tabla
            $browser->assertSee('3.0000');
        });

        $this->assertDatabaseHas('oms_datos_graficas', [
            'oms_catalogo_grafica_id' => $grafica->id,
        ]);
    }

    // =========================================================================
    // TEST 11: CONTROL DE ACCESO POR ROL (POLICIES)
    // =========================================================================

    /**
     * Verifica que las policies se aplican correctamente en el navegador:
     *
     *  - Un usuario sin rol Doctor NO puede acceder a crear consultas (403).
     *  - Un Doctor SÍ puede acceder y ver el formulario.
     *  - Un Admin también puede acceder (tiene permiso por política).
     */
    public function test_11_politicas_de_acceso_por_rol(): void
    {
        $doctorRole = Role::firstOrCreate(['name' => 'Doctor', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        // Usuario sin ningún rol
        User::factory()->create([
            'email' => 'sinrol@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Doctor con perfil de médico
        $doctor = Doctor::factory()->create([
            'full_name' => 'Dra. Prueba Políticas',
            'specialty' => 'Pediatría',
            'license_number' => 'MP-POLICY01',
        ]);
        $userDoctor = User::factory()->create([
            'email' => 'doctorpolicy@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'doctor_id' => $doctor->id,
        ]);
        $userDoctor->assignRole($doctorRole);

        // Admin
        $admin = User::factory()->create([
            'email' => 'adminpolicy@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($adminRole);
        $patient = Patient::factory()->create();

        $this->browse(function (Browser $browser) use ($patient) {
            // ── ESCENARIO A: usuario sin rol no puede crear consulta ──────────
            $this->loginAs($browser, 'sinrol@test.com');

            $browser
                ->visit('/pacientes/'.$patient->id)
                ->waitFor('@create-consultation', 10)
                ->click('@create-consultation')
                ->waitForText('403', 10);

            // ── ESCENARIO B: doctor SÍ puede crear consulta ───────────────────
            // Nueva sesión
            $browser->driver->manage()->deleteAllCookies();
            $this->loginAs($browser, 'doctorpolicy@test.com');

            $browser
                ->visit('/pacientes/'.$patient->id)
                ->waitFor('@create-consultation', 10)
                ->assertSee('Nueva Consulta');

            // ── ESCENARIO C: admin también puede crear consulta ───────────────
            $browser->driver->manage()->deleteAllCookies();
            $this->loginAs($browser, 'adminpolicy@test.com');

            $browser
                ->visit('/pacientes/'.$patient->id)
                ->waitFor('@create-consultation', 10)
                ->assertSee('Nueva Consulta');
        });
    }

    // =========================================================================
    // TEST 12: EDAD CLÍNICA EXACTA EN PERFIL DE PACIENTE
    // =========================================================================

    /**
     * Verifica que la vista de paciente renderiza la edad clínica exacta
     * usando el AgeValueObject (días/semanas/meses/años).
     */
    public function test_12_muestra_edad_clinica_exacta_en_paciente(): void
    {
        User::factory()->create([
            'email' => 'edad@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $patient = Patient::factory()->create([
            'full_name' => 'Paciente Edad Exacta',
            'date_of_birth' => now()->subDays(14)->toDateString(),
        ]);

        $this->browse(function (Browser $browser) use ($patient) {
            $this->loginAs($browser, 'edad@test.com');

            $browser
                ->visit('/pacientes/'.$patient->id)
                ->waitForText('Paciente Edad Exacta', 10)
                ->assertSee('Edad')
                ->assertSee('14 días');
        });
    }

    // =========================================================================
    // TEST 13: PANEL DE GRÁFICAS DE CRECIMIENTO OMS EN PERFIL DE PACIENTE
    // =========================================================================

    /**
     * Verifica que el dashboard del paciente renderiza el panel "Gráficas de
     * Crecimiento OMS" con las gráficas disponibles según el sexo del paciente.
     * Prueba la integración del GrowthChartService con la vista.
     */
    public function test_13_muestra_panel_graficas_crecimiento_en_paciente(): void
    {
        User::factory()->create([
            'email' => 'graficas@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $patient = Patient::factory()->create([
            'full_name' => 'Paciente Graficas OMS',
            'gender' => 'F',
            'date_of_birth' => now()->subMonths(6)->toDateString(),
        ]);

        // Gráfica OMS para sexo F → debe aparecer en el panel del paciente
        OmsCatalogoGrafica::factory()->create([
            'codigo' => 'DUSK_HC_F_CRECIMIENTO',
            'nombre' => 'Perímetro Cefálico Niñas',
            'tipo_grafica' => 'perimetro_cefalico',
            'sexo' => 'F',
        ]);

        $this->browse(function (Browser $browser) use ($patient) {
            $this->loginAs($browser, 'graficas@test.com');

            $browser
                ->visit('/pacientes/'.$patient->id)
                ->waitForText('Paciente Graficas OMS', 10)
                ->assertPresent('[dusk="growth-chart-panel"]')
                ->assertSee('Gráficas de Crecimiento OMS')
                ->assertSee('Perímetro Cefálico Niñas');
        });
    }

    // =========================================================================
    // TEST 14: CARGA INICIAL VÍA /pacientes/create-old + SCANS
    // =========================================================================

    /**
     * Flujo técnico de carga inicial de historia clínica antigua:
     *  1. El técnico ingresa a /pacientes/create-old y escribe solo el nombre.
     *  2. Hace clic en "Continuar" → aparece el paso 2 (scans).
     *  3. Sube N scans (PDF mínimo válido) uno por uno, verificando el contador.
     *  4. Hace clic en "Finalizar → ver paciente" → llega a pacientes.show.
     *  5. El historial muestra N filas con badge "Digitalizar".
     */
    public function test_14_carga_inicial_via_create_old_con_n_scans(): void
    {
        User::factory()->create([
            'email' => 'tecnico@clinica.com',
            'password' => bcrypt('password'),
            'doctor_id' => null,
        ]);

        $n = 3; // número de scans a subir

        // Crear N archivos PDF mínimos válidos en el directorio temporal del sistema
        $tmpFiles = [];
        for ($i = 1; $i <= $n; $i++) {
            $path = sys_get_temp_dir()."/scan_{$i}_".uniqid().'.pdf';
            file_put_contents($path, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\nxref\n0 1\n0000000000 65535 f\ntrailer<</Size 1/Root 1 0 R>>\nstartxref\n9\n%%EOF");
            $tmpFiles[] = $path;
        }

        // Nombre ASCII puro para evitar problemas de encoding con sendKeys()
        $patientName = 'Lorenzo Mendez Rojas';

        try {
            $this->browse(function (Browser $browser) use ($n, $tmpFiles, $patientName) {
                // ── 1. Login como técnico ─────────────────────────────────────
                $this->loginAs($browser, 'tecnico@clinica.com');

                // ── 2. Navegar a create-old y escribir nombre ─────────────────
                $browser
                    ->visit('/pacientes/create-old')
                    ->waitFor('[dusk="input-full-name-old"]', 10)
                    ->pause(500) // dejar que Alpine/Livewire se inicialice
                    ->type('[dusk="input-full-name-old"]', $patientName)
                    ->pause(300); // dejar que wire:model sincronice el valor

                // ── 3. Hacer clic en "Continuar" y esperar el paso de scans ───
                // JS click para evitar ElementClickInterceptedException del sidebar Flux
                $browser->script("document.querySelector('[dusk=\"btn-continuar-old\"]').click()");

                // Esperar el input de fecha que solo existe en el paso 2
                $browser->waitFor('[dusk="input-scan-date-old"]', 15);

                // ── 4. Subir N scans uno por uno ──────────────────────────────
                foreach ($tmpFiles as $index => $tmpFile) {
                    $scanDate = now()->subDays($n - $index)->format('Y-m-d');

                    // Esperar a que el formulario esté disponible (Livewire re-render)
                    $browser->waitFor('[dusk="input-scan-date-old"]', 10);
                    $browser->pause(500);

                    // Setear la fecha via JS nativo (->type() no funciona bien con input[date] tras reset Livewire)
                    $browser->script("
                        const dateInput = document.querySelector('[dusk=\"input-scan-date-old\"]');
                        const nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                        nativeInputValueSetter.call(dateInput, '{$scanDate}');
                        dateInput.dispatchEvent(new Event('input', { bubbles: true }));
                        dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                    ");
                    $browser->pause(300);

                    // Adjuntar el archivo — Livewire WithFileUploads lo sube al temp del servidor
                    $browser->attach('[dusk="input-scan-file-old"]', $tmpFile);

                    // Esperar a que desaparezca el indicador de carga del archivo
                    $browser->waitUntilMissingText('Subiendo archivo...', 20);
                    $browser->pause(500);

                    // Hacer clic en "Subir este scan"
                    $browser->script("document.querySelector('[dusk=\"btn-subir-scan-old\"]').click()");

                    // Esperar confirmación del contador de scans subidos
                    $uploaded = $index + 1;
                    $browser->waitForText("Consultas cargadas ({$uploaded})", 25);
                    $browser->pause(500);
                }

                // ── 5. Hacer clic en "Finalizar → ver paciente" ───────────────
                $browser->script("document.querySelector('[dusk=\"btn-finalizar-old\"]').click()");
                $browser->waitForText($patientName, 15);

                // ── 6. Verificar nombre y N filas en el historial ─────────────
                $browser
                    ->waitFor('[dusk="section-historial-consultas"]', 10)
                    ->assertSee($patientName)
                    ->assertSee('Historial de Consultas');

                $rows = $browser->elements('[dusk="section-historial-consultas"] tbody tr');
                $this->assertCount($n, $rows);
            });
        } finally {
            // Limpiar archivos temporales independientemente del resultado
            foreach ($tmpFiles as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        $this->assertDatabaseHas('patients', ['full_name' => $patientName]);
        $this->assertDatabaseCount('consultations', $n);
    }
}
