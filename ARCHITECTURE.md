# Arquitectura de Clínica Cumpito

## Visión General

Clínica Cumpito implementa una **Arquitectura Hexagonal (Puertos y Adaptadores)** para mantener una separación clara de responsabilidades y facilitar el testing, mantenimiento y escalabilidad del sistema.

## Capas Arquitectónicas

### 1. **Domain Layer** (Capa de Dominio)
Contiene la lógica de negocio pura e independiente del framework.

**Ubicación:** `app/Models/`

**Entidades (23 modelos):**
- `User.php` - Usuario del sistema con soporte de autenticación de dos factores (Fortify)
- `Doctor.php` - Médicos de la clínica
- `Patient.php` - Pacientes pediátricos con historial médico
- `MedicalCondition.php` - Catálogo de condiciones médicas (Chagas, Sífilis, etc.)
- `Consultation.php` - Consultas médicas (digital/manual, con estado draft/saved/finalized)
- `VitalSign.php` - Signos vitales de las consultas
- `SoapNote.php` - Notas clínicas en formato SOAP
- `LaboratoryCategory.php`, `LaboratoryExam.php`, `LaboratoryExamParameter.php` - Catálogo jerárquico de laboratorio
- `Vaccine.php` - Catálogo de vacunas (PAI Bolivia)
- `PrescriptionTemplate.php`, `PrescriptionTemplateItem.php` - Plantillas de recetas ("combos")
- `Prescription.php`, `PrescriptionItem.php` - Recetas emitidas (snapshot inmutable)
- `LaboratoryRequest.php`, `LaboratoryRequestItem.php` - Solicitudes de laboratorio (snapshot inmutable)
- `LaboratoryItemResult.php` - Resultado por parámetro de un ítem de laboratorio
- `LaboratoryAttachment.php` - Adjuntos (imagen/PDF) de una solicitud o ítem de laboratorio
- `PatientVaccine.php` - Vacunas aplicadas a un paciente
- `OmsCatalogoGrafica.php`, `OmsDatoGrafica.php` - Boletas OMS y sus puntos LMS/Z-Score/percentiles
- `ClinicSetting.php` - Configuración de la clínica (nombre, logo, contacto) para membretes impresos
- `AuditLog.php` - Bitácora de auditoría (quién/cuándo/qué cambió) en entidades críticas

**Características:**
- Uso de UUIDs como identificadores primarios
- Soft deletes para auditoría
- Relaciones polimórficas y uno-a-muchos bien definidas
- Traits para comportamientos comunes

---

### 2. **Schema Layer** (Capa de Esquema)
Define la estructura de datos persistente en la base de datos.

**Ubicación:** `database/migrations/` (36 migraciones)

Cubre: autenticación (`users` + 2FA), médicos, pacientes y condiciones médicas, consultas
(SOAP + signos vitales), catálogos de laboratorio y vacunas, plantillas de recetas/laboratorio,
ejecución real (recetas, solicitudes de laboratorio, resultados por parámetro, adjuntos),
boletas OMS, configuración de la clínica, auditoría y las tablas de `spatie/laravel-permission`.

Esquema completo y relaciones: ver [`dbdiagram.md`](dbdiagram.md) (generado desde las
migraciones reales, no es un documento de planificación).

**Características:**
- Todas las tablas de dominio usan UUID como primary key (única excepción: `clinic_settings`, registro único con id autoincremental)
- Foreign keys con constraint rules (cascade, restrict, set null)
- Timestamps e índices organizados; soft deletes en entidades con relevancia legal/histórica

---

### 3. **Test Fixtures Layer** (Capa de Fixtures de Testing)
Proporciona datos de prueba consistentes y realistas.

**Ubicación:** `database/factories/`

**Factories:**
- `UserFactory.php` - Genera usuarios válidos con credenciales fake
- `DoctorFactory.php` - Genera médicos con especialidades y licencias únicas
- `PatientFactory.php` - Genera pacientes con datos demográficos completos
- `ConsultationFactory.php` - Genera consultas con estados variados
- `VitalSignFactory.php` - Genera signos vitales con valores realistas
- `SoapNoteFactory.php` - Genera notas SOAP con contenido de ejemplo

**Testing:**
- `tests/Unit/Factories/` - Tests unitarios para cada factory
- `tests/Feature/Settings/` - Tests de funcionalidades (Two-Factor Auth, Profile, etc.)

---

### 4. **Application Layer** (Capa de Aplicación)
Orquesta la lógica de negocio implementando casos de uso. Controladores y componentes Livewire/Volt solo coordinan; no contienen reglas de negocio.

**Ubicación:** `app/Services/`

**Servicios (17):** `PacienteService`, `ConsultationService`, `ConsultationSnapshotService` (copia inmutable de plantillas a transacciones), `DoctorService`, `CatalogService` (catálogos de laboratorio/vacunas/medicamentos), `VitalSignService`, `SoapNoteService`, `PrescriptionTemplateService`, `PrescriptionService`, `PrescriptionItemService`, `PatientVaccineService`, `LaboratoryRequestService`, `LaboratoryRequestItemService`, `LaboratoryItemResultService`, `GrowthChartService` (datasets OMS para Chart.js), `ZScoreService` (fórmula LMS OMS), `ScannedConsultationService` (consultas manuales con archivo adjunto), `ClinicalDocumentService` (generación de PDF de recetas/órdenes).

---

### 5. **Ports Layer** (Capa de Puertos)
Define contratos (interfaces) entre capas; cada servicio de negocio tiene su contrato.

**Ubicación:** `app/Contracts/`

Un `*ServiceContract` por cada servicio de la capa de aplicación (17 en total), con binding a su implementación en `AppServiceProvider`.

---

### 6. **DTOs Layer** (Capa de Data Transfer Objects)
Encapsula datos para transferencia entre capas.

**Ubicación:** `app/DTOs/`

**DTOs:** `PacienteDTO`, `ConsultationDTO`, `DoctorDTO`, `VitalSignDTO`, `SoapNoteDTO`, `PrescriptionDTO`, `PrescriptionItemDTO`, `MedicationItemDTO`, `PatientVaccineDTO`, `LaboratoryRequestDTO`, `LaboratoryRequestItemDTO`, `LaboratoryItemResultDTO`, `LabStudyItemDTO`, `ClinicalDocumentDTO`, además de subcarpetas `Catalogs/` y `Templates/` con DTOs específicos de esos módulos.

---

### 7. **Value Objects Layer** (Capa de Value Objects)
Encapsula comportamiento y validación de conceptos del dominio, sin necesidad de identidad única.

**Ubicación:** `app/ValueObjects/`

**Value Objects implementados:**
- `Age.php` - Edad clínica exacta (días/semanas/meses/años) con formato de visualización
- `BloodGroup.php` - Grupo sanguíneo (8 tipos OMS)
- `Gender.php` - Género (M, F)
- `BirthType.php` - Tipo de parto (Normal, Cesarean)
- `MedicalStatus.php` - Estado médico (Positive, Negative, Not tested) para Chagas/Sífilis
- `ConsultationType.php` - Tipo de consulta (digital, manual)
- `ConsultationStatus.php` - Estado de consulta (draft, saved, finalized)
- `LicenseNumber.php` - Número de licencia médica con validación
- `PhoneNumber.php` - Número de teléfono con formato
- `PaperSize.php` - Tamaño de papel para impresión de documentos clínicos
- `ZScore.php` - Z-Score OMS con categoría clínica y rango normal
- `Measurements/` - Value Objects de medición (peso, talla, perímetro cefálico, temperatura) con validación de rango

**Características:**
- Encapsulación de lógica de validación
- Conversión de tipos con casting automático en modelos
- Métodos helper para operaciones comunes
- Immutables por defecto
- Comparable y serializable

---

### 7.1 **Policies** (Autorización por rol)

**Ubicación:** `app/Policies/`

`ConsultationPolicy`, `PatientPolicy`, `PrescriptionTemplatePolicy`, `LaboratoryTemplatePolicy`, `CatalogPolicy` — registradas junto con `AuthorizesRequests` explícito en el `Controller` base (Laravel 12 ya no lo incluye por defecto) y `Gate::define('manage-catalog')` en `AppServiceProvider`.

---

### 8. **Presentation Layer** (Capa de Presentación)
Interfaces de usuario y APIs.

**Ubicación:** `resources/views/`, `app/Http/Controllers/`

**Componentes:**
- Blade templates para vistas
- **Componentes Volt de archivo único** (Livewire 4): páginas en `resources/views/pages/**/⚡nombre.blade.php`, embebidos en `resources/views/components/⚡nombre.blade.php` (el `⚡` es literal en el nombre de archivo); rutas registradas con `Route::livewire(...)`
- Form requests para validación
- Controllers para orquestar requests (`PacienteController`, `ConsultationController`, `ClinicalDocumentController` para PDFs de recetas/laboratorios, etc.)

---

## Flujo de Datos

```
User Request
    ↓
[Controller/Route]
    ↓
[Request Validation]
    ↓
[Service Layer] ← [Repository/Query Builder]
    ↓
[Domain Models]
    ↓
[Database Layer]
    ↓
Response
```

## Convenciones de Código

### Naming Conventions
- **Models:** CamelCase singular (User, Doctor, Patient)
- **Tables:** snake_case plural (users, doctors, patients)
- **Migrations:** timestamp + descripción snake_case
- **Services:** NombreService.php
- **DTOs:** NombreDTO.php
- **Value Objects:** NombreCompleto.php
- **Contracts:** NombreContract.php

### Directory Structure
```
app/
├── Models/              # Entidades del dominio
├── Services/            # Lógica de aplicación
├── DTOs/                # Objetos de transferencia
├── ValueObjects/        # Value Objects del dominio
├── Contracts/           # Interfaces/Puertos
├── Http/
│   └── Controllers/     # Controllers HTTP
└── Providers/           # Service providers

database/
├── migrations/          # Esquema de BD
├── factories/           # Test fixtures
└── seeders/             # Datos iniciales

tests/
├── Unit/
│   └── Factories/       # Tests de factories
├── Feature/             # Tests funcionales
└── Pest.php             # Configuración Pest
```

## Principios SOLID

### Single Responsibility
Cada clase tiene una única razón para cambiar.

### Open/Closed
Abierto para extensión, cerrado para modificación.

### Liskov Substitution
Las implementaciones respetan los contratos de sus interfaces.

### Interface Segregation
Interfaces específicas por caso de uso.

### Dependency Inversion
Dependencias invertidas mediante inyección.

## Testing Strategy

- **551 tests pasando** (2279 assertions) vía `php artisan test --parallel`, más 14 tests de navegador (Laravel Dusk) en `tests/Browser/ClinicalWorkflowTest.php`.
- **Unit:** `tests/Unit/Factories/`, `tests/Unit/ValueObjects/`, `tests/Unit/Services/`, `tests/Unit/Policies/`.
- **Feature:** `tests/Feature/Auth/`, `tests/Feature/Settings/`, `tests/Feature/Seeders/` y un directorio por módulo clínico (pacientes, consultas, recetas, laboratorio, vacunas, catálogos, gráficas OMS).
- **Test Database:** `RefreshDatabase` con SQLite en memoria (`phpunit.xml`); Dusk corre contra PostgreSQL real (`vitaltrack_dusk`).
- **Regla del proyecto (no negociable):** todo cambio de código exige ≥1 test unitario y ≥1 test feature (ver [`Roadmap de Desarrollo.md`](Roadmap%20de%20Desarrollo.md) §"Política de pruebas").

## Estado de Implementación

El MVP contractual (fases 1 a 7 del roadmap) está **completo**: modelos, migraciones, factories,
value objects, servicios/contratos/DTOs, policies, componentes Livewire Volt y el motor de
gráficas OMS están todos implementados y probados. El proyecto tuvo demo con la clienta y
actualmente está en una ronda de correcciones y mejoras post-demo (documentos PDF imprimibles,
resultados de laboratorio por parámetro con adjuntos, configuración de marca de la clínica).
Detalle fase por fase, con fechas y evidencia: [`Roadmap de Desarrollo.md`](Roadmap%20de%20Desarrollo.md).

## Stack Tecnológico

- **Framework:** Laravel 12 (PHP 8.4+)
- **Database:** PostgreSQL 17
- **Testing:** Pest PHP + Laravel Dusk, PHPStan/Larastan nivel 8, Laravel Pint
- **ORM:** Eloquent
- **Validation:** Laravel Form Requests
- **Frontend:** Blade + Livewire 4 (Volt de archivo único) + Flux UI + Alpine.js
- **Styling:** Tailwind CSS 4
- **PDF:** barryvdh/laravel-dompdf
- **Autenticación/Autorización:** Laravel Fortify (2FA) + Sanctum + Spatie Laravel Permission

## Próximos Pasos

Ver "Prioridades actuales" en [`AGENTS.md`](AGENTS.md) y las fases pendientes (7.4 Pruebas de
precisión OMS, 8.2/8.3 Autorización y regresión, Fase 9 Despliegue y Capacitación) en
[`Roadmap de Desarrollo.md`](Roadmap%20de%20Desarrollo.md).

---

**Última actualización:** 10 de agosto de 2026
**Versión:** 1.1.0
**Estado:** MVP en producción, en refinamiento post-demo
