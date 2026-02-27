# 🚀 VitalTrack: Roadmap de Desarrollo (Laravel 12 + PHP 8.4)

Este documento detalla las tareas necesarias para construir el sistema, priorizando:

- calidad de código,
- cumplimiento de principios SOLID,
- cobertura de pruebas,
- y el **alcance contractual del MVP (20+ clases/módulos)** solicitado por cliente.

> **Regla contractual**: el MVP **no se reduce** sin aprobación explícita del cliente.

---

## 📜 Criterios de Desarrollo Obligatorios (Aplican a Todas las Subtareas)

### 1) Arquitectura (Laravel 12 + DDD ligero)

- **Modelos Eloquent**: solo persistencia, relaciones, scopes, casts y accessors/mutators simples.
- **Lógica de negocio**: debe vivir en `app/Actions`, `app/Services` o `app/ValueObjects`.
- **Controladores / Livewire**: coordinan flujo; no deben contener reglas de negocio complejas.
- **Contratos**: usar `app/Contracts` cuando el desacople aporte valor real (evitar sobreingeniería).
- **Regla de carpetas**: respetar estructura actual del proyecto.

### 2) Principios SOLID (Criterio de aceptación)

- **S**: una clase = una razón de cambio.
- **O**: extender por composición/estrategias.
- **L**: contratos consistentes.
- **I**: interfaces pequeñas por caso de uso.
- **D**: depender de abstracciones, no de concreciones.

### 3) Estilo y consistencia de código

- **PSR-12 + Laravel Pint** obligatorio antes de cerrar una tarea.
- **PHPStan** en el nivel definido por el proyecto, sin violaciones críticas nuevas.
- **Nombres explícitos**: evitar nombres ambiguos.
- **Sin duplicación**: extraer lógica repetida.
- **Blade/Tailwind**: sin estilos inline.

### 4) Reglas específicas del dominio clínico

- **Inmutabilidad histórica**: una consulta cerrada no se edita; se versiona o se guarda snapshot.
- **Datos sensibles**: validación estricta + policies por rol.
- **Trazabilidad**: acciones críticas auditables (quién, cuándo, qué cambió).

### 5) Política de pruebas obligatoria (No negociable)

- Toda implementación nueva (modelo, acción, servicio, componente Livewire, endpoint o regla de dominio) debe incluir:
  - mínimo 1 test unitario,
  - mínimo 1 test de integración/feature.
- Toda modificación de código existente debe actualizar tests afectados.

### 6) Definición de Terminado (Definition of Done)

Una subtarea se considera terminada solo si cumple todo:

- [ ] Implementación funcional completa del alcance.
- [ ] Ciclo completo aplicado cuando corresponda: Migración + Modelo + Factory + Servicio/Action.
- [ ] Pruebas unitarias + feature pasando.
- [ ] `php artisan test` sin fallos en el scope afectado.
- [ ] `./vendor/bin/pint` aplicado sin cambios pendientes.
- [ ] `./vendor/bin/phpstan analyse` sin regresiones críticas.
- [ ] Documentación mínima actualizada (roadmap/README/nota técnica).

### 7) Regla de alcance MVP

- [ ] Cumplir el **MVP contractual completo (20+ clases/módulos)**.
- [ ] No mover módulos MVP a post-MVP sin aprobación del cliente.

---

## 📅 Fase 1: Inicialización y Entorno

- [x] **1.1 Bootstrap del proyecto**: Laravel 12 + PHP 8.4.
- [x] **1.2 Git Flow**: configuración de repositorio.
- [x] **1.3 Configuración de DB**: entorno local configurado.
- [x] **1.4 CI/CD Setup**: pipeline de tests base.

## 🛠 Fase 2: Librerías y Herramientas Base

- [x] **2.1 Autenticación**: Fortify/Livewire starter.
- [x] **2.2 Permisos**: `spatie/laravel-permission`.
- [x] **2.3 Calidad**: Pint + PHPStan.
- [x] **2.4 Testing**: Pest como motor principal.

---

## ⚡ Fase 3: UI Base y Componentes (sin bloquear dominio) — 100%

- [x] **3.1 Tailwind/Design tokens**: configuración base del tema. ✔ `@tailwindcss/vite` en `vite.config.js`, CSS base en `resources/css/`.
- [x] **3.2 Layouts maestros**: Dashboard, Sidebar, Navbar. ✔ Layout dashboard + `layouts/app/sidebar.blade.php` + navbar maestro desktop.
- [x] **3.3 Componentes atómicos**: Inputs, Buttons, Alerts, Modals. ✔ Componentes en `resources/views/components/ui/{input,button,alert,modal}.blade.php` e integrados en vistas de pacientes.

---

## 🧱 Fase 4: MVP Contractual Completo (20+ clases/módulos)

> Ciclo obligatorio por CRUD: Migración + Modelo + Factory + Action/Service + Test Unitario + Test Feature.

### 4.1 Perfil Médico y Roles — 100%

- [x] **4.1.1 Perfil Doctor** (Especialidad, Matrícula Profesional). s— 100% ✔ Modelo `Doctor` + `DoctorDTO` + `DoctorService` + `UpdateDoctorRequest` + Componente Livewire Volt `⚡doctor-profile` + `DoctorProfileTest` (feature) + `DoctorServiceTest` (unit).

### 4.2 Catálogos Clínicos (Prioridad temprana solicitada) — 100%

- [x] **4.2.1 CRUD CategoriaLaboratorio** (Hematología, Orina, Imágenes, etc.). — 100% ✔ Migraciones + Modelo + Factory + DTO + Servicio + UI Livewire + tests feature de catálogos + `CatalogServiceTest` (unit).
- [x] **4.2.2 CRUD CatalogoExamenLaboratorio** (exámenes individuales). — 100% ✔ Migraciones + Modelo + Factory + DTO + Servicio + UI Livewire + tests feature de catálogos + `CatalogServiceTest` (unit).
- [x] **4.2.3 CRUD CatalogoMedicamento** (catálogo para recetas). — 100% ✔ Migraciones + Modelo + Factory + DTO + Servicio + UI Livewire + tests feature de catálogos + `CatalogServiceTest` (unit).
- [x] **4.2.4 CRUD CatalogoVacuna** (PAI Bolivia). — 100% ✔ Migraciones + Modelo + Factory + DTO + Servicio + UI Livewire + Seeder PAI Bolivia + tests feature de catálogos + `CatalogServiceTest` (unit).

### 4.3 Módulo Pacientes — 100%

- [x] **4.3.1 CRUD Paciente** (filiación, nacimiento, antecedentes). — 100% ✔ Migración + Modelo (`Patient.php`) + Factory + `PacienteService` + `PacienteServiceContract` + `PacienteDTO` + `PacienteController` + `StorePacienteRequest` + `UpdatePacienteRequest` + Vistas (`index/create/edit/show`) + `PacienteControllerTest` (feature) + `PatientFactoryTest` (unit) + Componente Livewire de búsqueda/gestión activa (`⚡patient-list.blade.php`).

### 4.4 Módulo Plantillas (Ahorro de tiempo) — 100%

- [x] **4.4.1 CRUD PlantillaReceta**. — 100% ✔ `PrescriptionTemplate` + migración + factory + `PrescriptionTemplateDTO` + `TemplateService` + UI (`resources/views/pages/templates/⚡prescription-templates.blade.php`) + `TemplateServiceTest` (unit) + `TemplateModuleTest` (feature).
- [x] **4.4.2 CRUD ItemPlantillaReceta**. — 100% ✔ `PrescriptionTemplateItem` + migración + factory + `PrescriptionTemplateItemDTO` + pruebas unitarias de servicio + `TemplateModuleTest` (feature) para flujo con ítems.
- [x] **4.4.3 CRUD PlantillaLaboratorio**. — 100% ✔ `LaboratoryTemplate` + migración + factory + `LaboratoryTemplateDTO` + UI (`resources/views/pages/templates/⚡laboratory-templates.blade.php`) + `TemplateServiceTest` (unit) + `TemplateModuleTest` (feature).
- [x] **4.4.4 CRUD ItemPlantillaLaboratorio**. — 100% ✔ `LaboratoryTemplateItem` + migración + factory + `LaboratoryTemplateItemDTO` + pruebas unitarias de servicio + `TemplateModuleTest` (feature) para flujo con ítems.

### 4.5 Flujo de Consulta (SOAP) — 100%

- [x] **4.5.1 CRUD Consulta** (digital/manual, estado). — 100% ✔ Migración + Modelo (`Consultation.php`, casts `ConsultationType`/`ConsultationStatus`) + Factory + `ConsultationService` + `ConsultationServiceContract` + `ConsultationDTO` + `ConsultationController` + `StoreConsultationRequest` + `UpdateConsultationRequest` + Vistas (`consultas/index/create/show/edit`) + `ConsultationServiceTest` (unit) + `ConsultationControllerTest` (feature) + navegación en sidebar/navbar.
- [x] **4.5.2 CRUD SignosVitales** (1:1 con consulta). — 100% ✔ Migración + Modelo (`VitalSign.php`) + Factory + `VitalSignFactoryTest` + `VitalSignService` + `VitalSignServiceContract` + `VitalSignDTO` + `VitalSignController` + endpoints (`consultas/{consulta}/signos-vitales`) + `VitalSignServiceTest` (unit) + `VitalSignControllerTest` (feature) + UI clínica integrada en `consultas/show`.
- [x] **4.5.3 CRUD NotasSoap** (1:1 con consulta). — 100% ✔ Migración + Modelo (`SoapNote.php`) + Factory + `SoapNoteFactoryTest` + `SoapNoteService` + `SoapNoteServiceContract` + `SoapNoteDTO` + `SoapNoteController` + endpoints (`consultas/{consulta}/soap`) + `SoapNoteServiceTest` (unit) + `SoapNoteControllerTest` (feature) + UI integrada en `consultas/show`.
- [x] **4.5.4 CRUD VacunaPaciente** (aplicaciones reales). — 100% ✔ Migración (`patient_vaccines`) + Modelo (`PatientVaccine.php`) + Factory + `PatientVaccineFactoryTest` + `PatientVaccineService` + `PatientVaccineServiceContract` + `PatientVaccineDTO` + `PatientVaccineController` + endpoints (`consultas/{consulta}/vacunas-paciente`) + `StorePatientVaccineRequest` + `PatientVaccineServiceTest` (unit) + `PatientVaccineControllerTest` (feature) + UI integrada en `consultas/show`.

### 4.6 Resultados e Inmutabilidad (Snapshots) — 100%

- [x] **4.6.1 CRUD Receta**. — 100% ✔ Migración (`prescriptions`) + Modelo (`Prescription.php`) + Factory + `PrescriptionFactoryTest` + `PrescriptionService` + `PrescriptionServiceContract` + `PrescriptionDTO` + `StorePrescriptionRequest` + `PrescriptionController` + endpoints (`consultas/{consulta}/recetas`) + `PrescriptionServiceTest` (unit) + `PrescriptionControllerTest` (feature) + UI integrada en `consultas/show` con bloqueo por consulta finalizada.
- [x] **4.6.2 CRUD DetalleReceta** (snapshot inmutable). — 100% ✔ Migración (`prescription_items`) + Modelo (`PrescriptionItem.php`) + Factory + `PrescriptionItemFactoryTest` + `PrescriptionItemService` + `PrescriptionItemServiceContract` + `PrescriptionItemDTO` + `StorePrescriptionItemRequest` + `PrescriptionItemController` + endpoints (`consultas/{consulta}/recetas/detalles`) + `PrescriptionItemServiceTest` (unit) + `PrescriptionItemControllerTest` (feature) + UI integrada en `consultas/show`.
- [x] **4.6.3 CRUD SolicitudLaboratorio**. — 100% ✔ Migración (`laboratory_requests`) + Modelo (`LaboratoryRequest.php`) + Factory + `LaboratoryRequestService` + `LaboratoryRequestServiceContract` + `LaboratoryRequestDTO` + `StoreLaboratoryRequestRequest` + `LaboratoryRequestController` + endpoints (`consultas/{consulta}/laboratorios`) + `LaboratoryRequestServiceTest` (unit) + `LaboratoryRequestControllerTest` (feature) + UI integrada en `consultas/show`.
- [x] **4.6.4 CRUD DetalleSolicitudLaboratorio** (snapshot + resultado). — 100% ✔ Migración (`laboratory_request_items`) + Modelo (`LaboratoryRequestItem.php`) + Factory + `LaboratoryRequestItemService` + `LaboratoryRequestItemServiceContract` + `LaboratoryRequestItemDTO` + `StoreLaboratoryRequestItemRequest` + `LaboratoryRequestItemController` + endpoints (`consultas/{consulta}/laboratorios/detalles`) + `LaboratoryRequestItemServiceTest` (unit) + `LaboratoryRequestItemControllerTest` (feature) + UI integrada en `consultas/show`.

### 4.7 Motor OMS (catálogo y datos) — 100%

- [x] **4.7.1 CRUD OmsCatalogoGrafica** (boletas oficiales OMS). — 100% ✔ Migración (`oms_catalogo_graficas`) + Modelo (`OmsCatalogoGrafica.php`) + Factory + `OmsCatalogoGraficaDTO` + 4 métodos en `CatalogService`/`CatalogServiceContract` + UI Livewire Volt (`⚡oms-graficas.blade.php`) con búsqueda y modal + ruta `catalogs/oms-graficas` + card en índice + `OmsGraficasTest` (feature, 5 tests) + `OmsCatalogoGraficaServiceTest` (unit, 3 tests) + `test_09` Dusk. 268 tests PHP pasando · Pint ✔
- [x] **4.7.2 CRUD OmsDatoGrafica** (LMS, Z-Score, percentiles). — 100% ✔ Migración (`oms_datos_graficas`, FK cascadeOnDelete) + Modelo (`OmsDatoGrafica.php`, `$table` explícito, `belongsTo`) + relación `hasMany datos()` en `OmsCatalogoGrafica` + Factory (7 puntos LMS reales OMS) + `OmsDatoGraficaDTO` + 4 métodos en `CatalogService`/`CatalogServiceContract` + UI Livewire Volt (`⚡oms-datos.blade.php`) con mount por URL segment + botón "Ver Datos" en `⚡oms-graficas.blade.php` + ruta `catalogs/oms-datos/{graficaId}` + `OmsDatosTest` (feature, 5 tests) + `OmsDatoGraficaServiceTest` (unit, 3 tests). 276 tests PHP pasando · Pint ✔

### 4.8 Seguridad, policies y auditoría — 100%

- [x] **4.8.1 Policies por rol** (Admin/Doctor y alcance por paciente). — 100% ✔ 5 policies creadas (`ConsultationPolicy`, `PatientPolicy`, `PrescriptionTemplatePolicy`, `LaboratoryTemplatePolicy`, `CatalogPolicy`) + `AuthorizesRequests` en `Controller` base + `Gate::define('manage-catalog')` en `AppServiceProvider` + `authorize()` en `ConsultationController`, `PacienteController` + `authorize()` en templates Livewire + tests de policies (ConsultationPolicyTest: 8 tests, PatientPolicyTest: 6 tests, TemplatePolicyTest: 4 tests).
- [x] **4.8.2 Auditoría mínima** en acciones críticas de catálogos y consulta. — 100% ✔ Migración `audit_logs` (UUID, user_id nullable FK, action, auditable_type/id, old/new values JSONB, ip, user_agent) + `AuditLog` model + `Auditable` trait (`bootAuditable` con updating/created/updated/deleted hooks) aplicado a `Consultation` y `OmsCatalogoGrafica` + `AuditLogTest` (7 tests).

---

## 🧠 Fase 5: Lógica de Dominio (Services/Value Objects) — 100%

- [x] **5.1 AgeValueObject**: cálculo de edad exacta (días/semanas/meses/años). — 100% ✔ `Age.php` implementado con cálculo exacto por `days()/weeks()/months()/years()` y representación clínica `forDisplay()`; integración transversal en `Patient::age()` y uso en `pacientes/show`; pruebas `AgeTest` (unit, 5 tests) + `PacienteControllerTest` (feature) para visualización clínica.
- [x] **5.2 BloodGroupValueObject**: validación tipológica. — 100% ✔ `BloodGroup.php` completo (8 tipos, validación, Castable, Stringable) + `BloodGroupTest.php` (unit); integración transversal cerrada en módulo Pacientes con pruebas feature de `store/update/show` (creación y actualización con grupos válidos, rechazo de grupos inválidos, render en dashboard). Auditoría 27-02-2026: cobertura de edge cases completada — test de `blood_group: null` (campo nullable), test parametrizado con los 8 grupos sanguíneos OMS (`->with([...])`), test de limpieza de grupo al actualizar.
- [x] **5.3 ZScoreService/ValueObject**: cálculo clínico OMS. — 100% ✔ `ZScore.php` (ValueObject con categoría clínica, rango normal y redondeo) + `ZScoreServiceContract` + `ZScoreService` con fórmula LMS OMS (`L=0` y `L≠0`) + cálculo por punto OMS más cercano (`x_value`) usando `oms_datos_graficas` + binding DI en `AppServiceProvider`; pruebas `ZScoreTest` (unit), `ZScoreServiceTest` (unit) y `ZScoreServiceFeatureTest` (feature con datos persistidos). Auditoría 27-02-2026: cobertura negativa completada — tests de error para medición ≤0, M/S ≤0, z-score negativo (medición < M), y `ModelNotFoundException` cuando la gráfica no tiene datos OMS asociados.
- [x] **5.4 ConsultationSnapshotService**: copia inmutable de plantillas a transacciones. — 100% ✔ `ConsultationSnapshotServiceContract` + `ConsultationSnapshotService` con métodos para copiar plantillas a snapshots (receta/laboratorio), bloquear transacciones al finalizar consulta y validar ventana de 3 días para edición de resultados; binding DI en `AppServiceProvider`; pruebas `ConsultationSnapshotServiceTest` (unit, 6 tests) + `ConsultationSnapshotFeatureTest` (feature, 1 test flujo completo).  — 100%
- [x] **5.5 GrowthChartService**: preparación de datasets para Chart.js. — 100% ✔ `GrowthChartServiceContract` (3 métodos tipados con PHPDoc) + `GrowthChartService` (fórmulas LMS por `tipo_grafica`: talla_edad, peso_edad, peso_talla, perimetro_cefalico, imc) + binding DI en `AppServiceProvider` + sección "Gráficas de Crecimiento OMS" en `pacientes/show.blade.php` (`dusk="growth-chart-panel"`) + `GrowthChartServiceTest` (unit, 5 tests) + `GrowthChartServiceFeatureTest` (feature, 1 test) + `test_13` Dusk.

---

## 📋 Fase 6: Integración de Flujo Clínico Completo (Livewire) — 50%

> **Redefinición 27-02-2026**: Las vistas existentes (consulta 742 líneas, paciente 517 líneas) eran páginas monolíticas con formularios HTML que recargaban. Rediseñadas como landing-pages con Livewire Volt reactivo.

- [x] **6.1 Dashboard del Paciente** (landing-page, 6 secciones). — 100% ✔ `pacientes/show.blade.php` reescrito con barra de anclas sticky + 6 secciones: (1) Header/Datos Base (edad exacta, género, grupo sanguíneo, alergias, antecedentes, condiciones), (2) Última Consulta (VS+SOAP+Rx+Lab en modo lectura), (3) Gráfica OMS con selector `⚡patient-oms-chart` (tabla de datapoints + placeholder Chart.js Fase 7), (4) Historial de Consultas (tabla con íconos de estado), (5) Historial de Recetas (agrupado por fecha), (6) Historial de Laboratorios (agrupado por fecha). `PacienteController::show()` con eager loading completo + `$latestConsultation`.
- [x] **6.2 Vista de Consulta Reactiva** (landing-page + Livewire). — 100% ✔ `consultas/show.blade.php` reescrito como landing-page con header estático + barra ancla + 5 Volt components independientes que guardan sin recargar: `⚡consultation-vital-signs`, `⚡consultation-soap-note`, `⚡consultation-prescription`, `⚡consultation-laboratory`, `⚡consultation-vaccines`. Cada componente: carga desde DB en `mount()`, badge reactivo (Sin datos / Guardado / Finalizada), bloqueo si `finalized`. Tests Dusk `08` actualizado (flash → badges Livewire).
- [ ] **6.3 Gestión de pacientes** (listado, crear, editar). — 0% · Ajustar UX del listado y formularios de creación/edición si necesario (baja prioridad — funcionalidad ya existe).
- [ ] **6.4 Módulo híbrido**: subida y visualización de PDF/JPG históricos (consultas manuales escaneadas). — 0%

---

## 📈 Fase 7: Motor Gráfico OMS — 75%

- [x] **7.1 Seeding masivo OMS**: importación Excel a `oms_datos_graficas`. — 100% ✔ `WhoDataSeeder` lee 50 archivos Excel OMS (`phpoffice/phpspreadsheet ^5.4`) y crea **10 `OmsCatalogoGrafica`** (5 tipos × 2 sexos: M/F) con todos sus `OmsDatoGrafica` (LMS + Z-Scores + percentiles P3/P15/P50/P85/P97). Casos especiales resueltos: (a) columna extra `SD` en lhfa/hcfa detectada por nombre dinámico, (b) merge 0-2 + 2-5 años sin duplicar mes 24 para `talla_edad`/`imc`, (c) merge wfl+wfh por corte a 65 cm para `peso_talla`. Unique index `(oms_catalogo_grafica_id, x_value)` agregado en migración original. Idempotente via `updateOrCreate`. `WhoDataSeeder` registrado en `DatabaseSeeder`. **12 tests feature** en `WhoDataSeederTest` (conteo 10 boletas, 2 por tipo, LMS no nulos, rangos x_value, merge correcto, sin duplicados, idempotencia).
- [x] **7.2 Gráficas Chart.js + 7.3 Modo dual** (implementados conjuntamente): — 100% ✔ `GrowthChartService::getReferenceDatasets()` extendido con `percentile_datasets` (P3/P50/P97) usando la constante `PERCENTILE_DATASETS`; `prepareChartData()` expone la nueva clave. `resources/js/app.js`: import `chart.js/auto` + `Alpine.data('omsChart', ...)` con modo `'padres'` por defecto (3 curvas percentil punteadas) y modo `'medico'` (7 curvas SD semáforo); función `zToPercentile()` (polinomio de Horner) para tooltip `~Percentil N`. `⚡patient-oms-chart.blade.php`: canvas con toggle Padres/Médico Alpine puro (`dusk="btn-modo-padres"` / `dusk="btn-modo-medico"`); listener `@oms-chart-data.window` para re-render tras cambio de gráfica Livewire; `wire:ignore` en wrapper. `npm run build` ✔ (208 KB JS · 264 KB CSS). `GrowthChartServiceTest` ampliado con `it('retorna percentile_datasets con P3, P50 y P97')` — 6 tests · 28 assertions pasando. **360 tests PHP · 13 Dusk · PHPStan ✔ · Pint ✔**.
- [ ] **7.4 Pruebas de precisión**: coordenadas de pacientes de prueba. — 0%

---

## 🧪 Fase 8: Calidad, Seguridad y Cierre Técnico — 34%

- [~] **8.1 Cobertura**: fortalecer suite de pruebas en módulos críticos. — 65% ✔ Tests unitarios para Value Objects (`BirthType`, `BloodGroup`, `ConsultationStatus`, `ConsultationType`, `Gender`, `LicenseNumber`, `Measurements/*`, `MedicalStatus`, `PhoneNumber`), Factories (`Doctor`, `Patient`, `Consultation`, `VitalSign`, `SoapNote`, `PatientVaccine`, `Prescription`, `PrescriptionItem`, `User`), `PacienteServiceTest`, `TemplateServiceTest`, `ConsultationServiceTest`, `DoctorServiceTest`, `CatalogServiceTest`, `VitalSignServiceTest`, `SoapNoteServiceTest`, `PatientVaccineServiceTest`, `PrescriptionServiceTest`, `PrescriptionItemServiceTest` y feature `ConsultationControllerTest` + `TemplateModuleTest` + `VitalSignControllerTest` + `SoapNoteControllerTest` + `PatientVaccineControllerTest` + `PrescriptionControllerTest` + `PrescriptionItemControllerTest`. Auditoría 27-02-2026: +40 tests de cobertura negativa y edge cases en `ZScoreServiceTest`, `ZScoreServiceFeatureTest` y `PacienteControllerTest` + `GrowthChartServiceTest` (5 unit) + `GrowthChartServiceFeatureTest` (1 feature) → **347 tests PHP · 13 Dusk**. ✘ Pendiente cobertura en snapshots de laboratorio, OMS, policies con restricción real y flujo clínico completo.
- [ ] **8.2 Pruebas de autorización**: acceso correcto por rol/propietario. — 0%
- [ ] **8.3 Pruebas de regresión** del flujo completo de consulta. — 0%

---

## 🚀 Fase 9: Despliegue y Capacitación — 0%

- [ ] **9.1 Preparación productiva**: servidor, colas, storage, backup. — 0%
- [ ] **9.2 Carga inicial**: escaneos históricos comprometidos. — 0%
- [ ] **9.3 Capacitación**: uso operativo con la doctora. — 0%

---

---

## 📊 Resumen de Auditoría — 24 Feb 2026

| Fase | Descripción | Avance |
|------|------------|--------|
| 1 | Inicialización y Entorno | ✅ 100% |
| 2 | Librerías y Herramientas Base | ✅ 100% |
| 3 | UI Base y Componentes | ✅ 100% |
| 4.1 | Perfil Médico (Doctor) | ✅ 100% |
| 4.2 | Catálogos Clínicos | ✅ 100% |
| 4.3 | Módulo Pacientes | ✅ 100% |
| 4.4 | Módulo Plantillas | ✅ 100% |
| 4.5 | Flujo de Consulta (SOAP) | ✅ 100% |
| 4.6 | Resultados / Snapshots | ✅ 100% |
| 4.7 | Motor OMS (datos) | ✅ 100% |
| 4.8 | Seguridad / Policies | ✅ 100% |
| 5 | Lógica de Dominio (VO/Services) | ✅ 100% |
| 6 | Livewire Clínico | 🟡 50% |
| 7 | Motor Gráfico OMS | 🟡 75% |
| 8 | Calidad / Cierre Técnico | 🟡 60% |
| 9 | Despliegue y Capacitación | 🔴 0% |
| **Total MVP** | **Fases 1–5 + Fase 6 parcial + Fase 7 parcial** | **~78%** |

> **Evidencia de auditoría**: `git status`, listado de `app/`, `database/migrations/`, `tests/`, `resources/views/`, `app/Livewire/`. Fecha: 24-02-2026.

### ✅ Actualización de ejecución (26-02-2026)

- Pre-commit ejecutado en verde: `./vendor/bin/pint`, `./vendor/bin/phpstan analyse`, `php artisan test`.
- Estado de calidad posterior al avance: **268 tests pasando** (Unit+Feature) + **9 tests Dusk implementados** y **PHPStan sin errores**.
- 4.7.1 completado: `OmsCatalogoGrafica` CRUD completo con Livewire Volt, 5 tests feature + 3 tests unit + test_09 Dusk.
- 4.7.2 completado: `OmsDatoGrafica` CRUD completo (método LMS, 17 columnas, hard delete), 5 tests feature + 3 tests unit + `test_10` Dusk. Bug resuelto: `$table` explícito en modelo porque Laravel infiere `oms_dato_graficas` en lugar de `oms_datos_graficas`.
- Estado actual: **276 tests PHP pasando** · **10 tests Dusk implementados** · Pint ✔ · DB Dusk migrada.
- Siguiente fase activa recomendada: **4.8 Seguridad, policies y auditoría**.

### ✅ Actualización de ejecución (26-02-2026) — Fase 4.8

- Pre-commit ejecutado en verde: `./vendor/bin/pint`, `./vendor/bin/phpstan analyse`, `php artisan test`.
- 4.8.1 completado: 5 policies + `AuthorizesRequests` en Controller base + `Gate::define` en AppServiceProvider + `authorize()` en controllers y templates Livewire + 18 tests de policies (8 ConsultationPolicy + 6 PatientPolicy + 4 TemplatePolicy).
- 4.8.2 completado: migración `audit_logs` + `AuditLog` model + `Auditable` trait (updating/created/updated/deleted hooks, captura old/new values) aplicado a `Consultation` y `OmsCatalogoGrafica` + 7 tests de AuditLog.
- Bug resuelto: `Controller` base en Laravel 12 no incluye `AuthorizesRequests` → agregado explícitamente.
- Estado actual: **301 tests PHP pasando** · **12 tests Dusk implementados** · Pint ✔ · PHPStan ✔.
- `DefaultUsersSeeder` creado con 3 usuarios por defecto: `admin@clinica.com` (Admin), `doctor@clinica.com` (Dr. Carlos García), `doctora@clinica.com` (Dra. Ana López).
- `test_11` Dusk: verifica policies en el navegador (sin rol = 403 sin form, Doctor/Admin = acceso a Nueva Consulta).
- `test_12` Dusk: valida visualización de edad clínica exacta en perfil de paciente (`14 días`) tras integrar `AgeValueObject`.
- 5.2 completado: evidencia de integración de `BloodGroup` en `PacienteControllerTest` (store/update/show + validación de rechazo para valores inválidos), manteniendo `BloodGroupTest` como base unitaria.
- 5.3 completado: implementado motor de cálculo LMS OMS (`ZScoreService` + `ZScore` VO) con pruebas unitarias y feature en verde (`8 tests passing`) + `phpstan` sin errores en los nuevos artefactos.
- 5.4 completado: motor de snapshots inmutables para consultas. `ConsultationSnapshotService` con métodos para copiar plantillas a receta/laboratorio (snapshot transaccional), bloqueo al finalizar FINALIZED, ventana de 3 días para llenar laboratorios. Unit (`6 tests`) + feature (`1 test flujo`) + `phpstan` sin errores.

### ✅ Actualización de ejecución (27-02-2026) — Fase 5.5 + Correcciones de infraestructura

- **5.5 completado**: `GrowthChartService` implementado con despacho por `tipo_grafica` usando fórmula LMS OMS. Retorna curvas de referencia SD (-3 a +3) y puntos del paciente con z-score y categoría clínica. Sección mínima UI en `pacientes/show.blade.php` con `dusk="growth-chart-panel"` para habilitar test de browser. PHPDoc `@return` con tipos shape (`array{...}`) para satisfacer PHPStan nivel estricto.
- **`DuskTestCase` corregido**: servidor dusk (`php artisan serve --env=dusk.local`) ahora se mata automáticamente al finalizar los tests via `tearDownAfterClass()` + `register_shutdown_function()` como red de seguridad. Usa `taskkill /F /T /PID` en Windows para matar árbol de procesos completo — evita servidores zombie que dejaban puerto 8000 ocupado con `vitaltrack_dusk` tras `npm run pre-commit`.
- Estado actual: **347 tests PHP pasando** · **13 tests Dusk** · PHPStan ✔ · Pint ✔.
- **Fase 5 cerrada al 100%**.

### ✅ Actualización de ejecución (27-02-2026) — Fase 6.1 + 6.2 completadas

- **6.1 completado**: `pacientes/show.blade.php` reescrito como landing-page con 6 secciones ancladas. `⚡patient-oms-chart` Volt component carga datos OMS desde DB en `mount()`, muestra tabla de datapoints con categorías z-score y placeholder Chart.js (Fase 7).
- **6.2 completado**: `consultas/show.blade.php` reescrito con header estático y 5 Volt components reactivos. Patrón clave: datos nunca se pasan como props `Collection` — se cargan desde DB en `mount()` para evitar `TypeError` de Livewire 4 (asigna props a propiedades antes de llamar `mount()`).
- **Bug crítico resuelto**: `ConsultationControllerTest` usaba `Consultation::factory()->create()` con `randomElement(['draft','saved','finalized'])` → test no determinista. Corregido con `['status' => 'draft']` explícito.
- **Test Dusk 08 actualizado**: mensajes de flash reemplazados por esperas a badges Livewire (`'Guardado'`) y texto de ítems (`'Hemograma completo'`).
- Estado actual: **347 tests PHP pasando** · **13 tests Dusk** · PHPStan ✔ · Pint ✔.
- Siguiente fase recomendada: **6.3** (UX listado/formularios pacientes) o saltar a **Fase 7** (Chart.js OMS).

### ✅ Actualización de ejecución (27-02-2026) — Fases 7.2 + 7.3 Gráficas Chart.js con modo dual

- **7.2 + 7.3 combinados y completados**: implementación conjunta para evitar retrabajo en el Alpine component y `GrowthChartService`.
- **`GrowthChartService`**: constante `PERCENTILE_DATASETS` (P3/P50/P97) + `percentile_datasets` expuesto en `getReferenceDatasets()` y `prepareChartData()`. Contract PHPDoc actualizado con el nuevo shape.
- **`resources/js/app.js`**: import `chart.js/auto` + función `zToPercentile()` (normal CDF polinomio de Horner) + `Alpine.data('omsChart', ...)` con modo `padres` por defecto (3 curvas percentil, tooltips `~Percentil N`) y modo `medico` (7 curvas SD semáforo, tooltips Z-Score + categoría). Re-render Alpine puro sin roundtrip Livewire al cambiar modo.
- **`⚡patient-oms-chart.blade.php`**: canvas `dusk="chart-canvas"` + toggle Padres/Médico con estilos teal activo / zinc inactivo; listener `@oms-chart-data.window` para re-render al cambiar selector de gráfica; `wire:ignore` en wrapper.
- **`GrowthChartServiceTest`**: nuevo test `it('retorna percentile_datasets con P3, P50 y P97')` validando count 3, labels, flags `dash`, y conteo de datos por punto OMS.
- **`npm run build`**: ✔ 208 KB JS · 264 KB CSS · 3.17s.
- Estado actual: **360 tests PHP pasando** · **13 tests Dusk** · PHPStan ✔ · Pint ✔.
- Siguiente fase recomendada: **7.4 Pruebas de precisión** (coordenadas de pacientes de prueba con datos OMS reales sembrados).

### ✅ Actualización de ejecución (27-02-2026) — Fase 7.1 WhoDataSeeder

- **7.1 completado**: `WhoDataSeeder` implementado con `phpoffice/phpspreadsheet ^5.4`. Importa 50 archivos Excel OMS y siembra **10 boletas** (5 tipos × 2 sexos) con datos LMS + SD + percentiles directos desde los estándares oficiales OMS.
- **Unique index** `(oms_catalogo_grafica_id, x_value)` agregado en la migración original `2026_02_26_000002`. No se creó migración separada — desarrollo activo permite `migrate:fresh`.
- **Casos especiales**: columna `SD` extra en lhfa/hcfa ignorada con detección dinámica de headers; merge 0-2+2-5 años para `talla_edad`/`imc` (archivo posterior sobreescribe mes 24 compartido); merge wfl+wfh para `peso_talla` cortando a x < 65 cm para wfl y x ≥ 65 cm para wfh → 76 puntos por sexo (45–120 cm en pasos de 1 cm).
- **12 tests feature** en `tests/Feature/Seeders/WhoDataSeederTest.php` verifican: 10 boletas, 2 por tipo, datos LMS presentes en 100%, meses 0-60 para tipos de edad, rango cm para peso_talla, merge sin duplicados, idempotencia (segunda ejecución sin cambios).
- Estado actual: **359 tests PHP pasando** · **13 tests Dusk** · PHPStan ✔ · Pint ✔.
- Siguiente fase recomendada: **7.2 Gráficas Chart.js** (conectar `GrowthChartService` con datos reales OMS ya sembrados).

### ✅ Actualización de ejecución (27-02-2026) — Auditoría Fase 5 + Cobertura negativa

- Auditoría de commits `d8fd82e` (BloodGroup tests) y `0d03faf` (ZScoreService) realizada con revisión de arquitectura SOLID, calidad de tests y edge cases.
- **5.2** — Cobertura edge cases completada en `PacienteControllerTest`: `blood_group: null` (nullable), 8 grupos sanguíneos parametrizados con `->with([...])`, limpiar grupo al actualizar → `null`.
- **5.3** — Cobertura negativa completada en `ZScoreServiceTest` (medición ≤0, M/S ≤0, z-score negativo) y `ZScoreServiceFeatureTest` (`ModelNotFoundException` con gráfica sin datos OMS). Confirmado: `ZScoreService::calculateByGrafica()` ya tenía guarda implementada.
- **Deuda técnica documentada**: `PatientPolicy` retorna `true` en todos los métodos → no testeable acceso denegado hasta implementar restricciones reales. `ZScore` ValueObject no implementa `Castable` (pendiente si se requiere cast en Eloquent).
- Estado actual: **341 tests PHP pasando** (759 assertions) · PHPStan ✔ · Pint ✔.

### ✅ Actualización de ejecución (26-02-2026) — Fase 5.1

- Revisión de consistencia realizada sobre los últimos 2 commits: `09b2579` (policies/auditoría) y `f7f04a5` (OMS datos), manteniendo patrón de implementación con evidencia en tests.
- 5.1 completado: `Age` Value Object con cálculo exacto de edad clínica por días/semanas/meses/años y formato legible.
- Integración de dominio: agregado `age()` en `Patient` y reemplazo de cálculo directo en vista de paciente por `forDisplay()`.
- Pruebas añadidas: `tests/Unit/ValueObjects/AgeTest.php` (5 tests) y caso feature en `PacienteControllerTest` para validar render de edad exacta.

---

## 📌 Orden recomendado de ejecución (sin recortar alcance)

1. **4.2 Catálogos clínicos** (lab + medicamentos) para habilitar flujos.
2. **4.3 + 4.5 Pacientes y Consulta** para atención real.
3. **4.4 + 4.6 Plantillas y snapshots** para ahorro de tiempo e inmutabilidad.
4. **4.7 OMS + Fase 7** para el componente gráfico completo.
5. **4.8 + Fase 8/9** para seguridad, cierre y salida a producción.

Este orden **no reduce** el MVP; solo optimiza la ejecución del alcance completo solicitado por cliente.