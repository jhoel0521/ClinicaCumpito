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

### 4.7 Motor OMS (catálogo y datos) — 0%

- [ ] **4.7.1 CRUD OmsCatalogoGrafica** (boletas oficiales OMS). — 0%
- [ ] **4.7.2 CRUD OmsDatoGrafica** (LMS, Z-Score, percentiles). — 0%

### 4.8 Seguridad, policies y auditoría — 0%

- [ ] **4.8.1 Policies por rol** (Admin/Doctor y alcance por paciente). — 0% (no existe carpeta `app/Policies/`)
- [ ] **4.8.2 Auditoría mínima** en acciones críticas de catálogos y consulta. — 0%

---

## 🧠 Fase 5: Lógica de Dominio (Services/Value Objects) — 35%

- [ ] **5.1 AgeValueObject**: cálculo de edad exacta (días/semanas/meses/años). — 0%
- [~] **5.2 BloodGroupValueObject**: validación tipológica. — 90% ✔ `BloodGroup.php` completo (8 tipos, validación, Castable, Stringable) + `BloodGroupTest.php`; además existen `Gender`, `BirthType`, `ConsultationStatus`, `ConsultationType`, `LicenseNumber`, `PhoneNumber`, `MedicalStatus` y `Measurements/{Height,Weight,Temperature,HeadCircumference}` con tests unitarios. ✘ Pendiente evidencia de integración/uso transversal para cerrar con política estricta de unit + feature.
- [ ] **5.3 ZScoreService/ValueObject**: cálculo clínico OMS. — 0%
- [ ] **5.4 ConsultationSnapshotService**: copia inmutable de plantillas a transacciones. — 0%
- [ ] **5.5 GrowthChartService**: preparación de datasets para Chart.js. — 0%

---

## 📋 Fase 6: Integración de Flujo Clínico Completo (Livewire) — 0%

- [ ] **6.1 Gestión de pacientes**: listado, búsqueda, registro y edición. — 0% (solo existe `Livewire/Actions/Logout.php`; no hay componentes Livewire de dominio)
- [ ] **6.2 Atención activa**: SOAP + signos + aplicación de plantillas. — 0%
- [ ] **6.3 Recetas y laboratorios**: creación, edición permitida por estado, cierre final. — 0%
- [ ] **6.4 Módulo híbrido**: subida y visualización de PDF/JPG históricos. — 0%

---

## 📈 Fase 7: Motor Gráfico OMS — 0%

- [ ] **7.1 Seeding masivo OMS**: importación CSV a `oms_datos_graficas`. — 0%
- [ ] **7.2 Gráficas Chart.js**: boletas, radio buttons y rangos. — 0%
- [ ] **7.3 Modo dual**: Médico (Z-Score) vs Padres (Percentil). — 0%
- [ ] **7.4 Pruebas de precisión**: coordenadas de pacientes de prueba. — 0%

---

## 🧪 Fase 8: Calidad, Seguridad y Cierre Técnico — 34%

- [~] **8.1 Cobertura**: fortalecer suite de pruebas en módulos críticos. — 55% ✔ Tests unitarios para Value Objects (`BirthType`, `BloodGroup`, `ConsultationStatus`, `ConsultationType`, `Gender`, `LicenseNumber`, `Measurements/*`, `MedicalStatus`, `PhoneNumber`), Factories (`Doctor`, `Patient`, `Consultation`, `VitalSign`, `SoapNote`, `PatientVaccine`, `Prescription`, `PrescriptionItem`, `User`), `PacienteServiceTest`, `TemplateServiceTest`, `ConsultationServiceTest`, `DoctorServiceTest`, `CatalogServiceTest`, `VitalSignServiceTest`, `SoapNoteServiceTest`, `PatientVaccineServiceTest`, `PrescriptionServiceTest`, `PrescriptionItemServiceTest` y feature `ConsultationControllerTest` + `TemplateModuleTest` + `VitalSignControllerTest` + `SoapNoteControllerTest` + `PatientVaccineControllerTest` + `PrescriptionControllerTest` + `PrescriptionItemControllerTest`. ✘ Pendiente cobertura en snapshots de laboratorio, OMS, policies y flujo clínico completo.
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
| 4.7 | Motor OMS (datos) | 🔴 0% |
| 4.8 | Seguridad / Policies | 🔴 0% |
| 5 | Lógica de Dominio (VO/Services) | 🟡 30% |
| 6 | Livewire Clínico | 🔴 0% |
| 7 | Motor Gráfico OMS | 🔴 0% |
| 8 | Calidad / Cierre Técnico | 🟡 55% |
| 9 | Despliegue y Capacitación | 🔴 0% |
| **Total MVP** | **Fase 4 avanzando en snapshots** | **~50%** |

> **Evidencia de auditoría**: `git status`, listado de `app/`, `database/migrations/`, `tests/`, `resources/views/`, `app/Livewire/`. Fecha: 24-02-2026.

### ✅ Actualización de ejecución (24-02-2026)

- Pre-commit ejecutado en verde: `./vendor/bin/pint`, `./vendor/bin/phpstan analyse`, `php artisan test`.
- Estado de calidad posterior al avance: **260 tests pasando** (Unit+Feature) + **8 tests Dusk** y **PHPStan sin errores**.
- Siguiente fase activa recomendada: **4.7.1 CRUD OmsCatalogoGrafica**.

---

## 📌 Orden recomendado de ejecución (sin recortar alcance)

1. **4.2 Catálogos clínicos** (lab + medicamentos) para habilitar flujos.
2. **4.3 + 4.5 Pacientes y Consulta** para atención real.
3. **4.4 + 4.6 Plantillas y snapshots** para ahorro de tiempo e inmutabilidad.
4. **4.7 OMS + Fase 7** para el componente gráfico completo.
5. **4.8 + Fase 8/9** para seguridad, cierre y salida a producción.

Este orden **no reduce** el MVP; solo optimiza la ejecución del alcance completo solicitado por cliente.