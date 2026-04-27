# Arquitectura de VitalTrack

## Visión General

VitalTrack implementa una **Arquitectura Hexagonal (Puertos y Adaptadores)** para mantener una separación clara de responsabilidades y facilitar el testing, mantenimiento y escalabilidad del sistema.

## Capas Arquitectónicas

### 1. **Domain Layer** (Capa de Dominio)
Contiene la lógica de negocio pura e independiente del framework.

**Ubicación:** `app/Models/`

**Entidades:**
- `User.php` - Usuario del sistema con soporte de autenticación de dos factores
- `Doctor.php` - Médicos de la clínica
- `Patient.php` - Pacientes pediátricos con historial médico
- `Consultation.php` - Consultas médicas (digital/manual)
- `VitalSign.php` - Signos vitales de las consultas
- `SoapNote.php` - Notas clínicas en formato SOAP

**Características:**
- Uso de UUIDs como identificadores primarios
- Soft deletes para auditoría
- Relaciones polimórficas y uno-a-muchos bien definidas
- Traits para comportamientos comunes

---

### 2. **Schema Layer** (Capa de Esquema)
Define la estructura de datos persistente en la base de datos.

**Ubicación:** `database/migrations/`

**Migraciones:**
- `2025_02_22_000000_create_users_table.php` - Tabla usuarios con UUID
- `2025_02_22_000001_create_doctors_table.php` - Tabla médicos
- `2025_02_22_000002_create_patients_table.php` - Tabla pacientes con datos de nacimiento
- `2025_02_22_000003_create_consultations_table.php` - Tabla consultas
- `2025_02_22_000004_create_vital_signs_table.php` - Tabla signos vitales
- `2025_02_22_000005_create_soap_notes_table.php` - Tabla notas SOAP

**Características:**
- Todas las tablas usan UUID como primary key
- Foreign keys con constraint rules (cascade, restrict, set null)
- Timestamps e índices organizados

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

### 4. **Application Layer** (Capa de Aplicación - Próxima Fase)
Orquesta la lógica de negocio implementando casos de uso.

**Ubicación:** `app/Services/` (a implementar)

**Servicios Planeados:**
- `PacienteService` - Gestión de pacientes
- `ConsultaService` - Gestión de consultas
- `VacunaService` - Gestión de vacunaciones
- `PlantillaService` - Gestión de plantillas médicas
- `GraficasOmsService` - Cálculo de gráficas de la OMS

---

### 5. **Ports Layer** (Capa de Puertos - Próxima Fase)
Define contratos (interfaces) entre capas.

**Ubicación:** `app/Contracts/` (a implementar)

**Interfaces Planeadas:**
- `PacienteServiceContract` - Contrato del servicio de pacientes
- `ConsultaServiceContract` - Contrato del servicio de consultas
- `VacunaServiceContract` - Contrato del servicio de vacunas
- `PlantillaServiceContract` - Contrato del servicio de plantillas
- `GraficasOmsServiceContract` - Contrato del servicio de gráficas

---

### 6. **DTOs Layer** (Capa de Data Transfer Objects - Próxima Fase)
Encapsula datos para transferencia entre capas.

**Ubicación:** `app/DTOs/` (a implementar)

**DTOs Planeados:**
- `PacienteDTO` - Transferencia de datos de pacientes
- `ConsultaDTO` - Transferencia de datos de consultas
- `VacunaDTO` - Transferencia de datos de vacunaciones
- `PlantillaDTO` - Transferencia de datos de plantillas
- `SignoVitalDTO` - Transferencia de datos de signos vitales
- `NotaSoapDTO` - Transferencia de datos de pacientes
- `ConsultaDTO` - Transferencia de datos de consultas
- `VacunaDTO` - Transferencia de datos de vacunaciones
- `PlantillaDTO` - Transferencia de datos de plantillas
- `SignoVitalDTO` - Transferencia de datos de signos vitales
- `NotaSoapDTO` - Transferencia de datos de notas SOAP

---

### 7. **Value Objects Layer** (Capa de Value Objects)
Encapsula comportamiento y validación de conceptos del dominio, sin necesidad de identidad única.

**Ubicación:** `app/ValueObjects/`

**Value Objects Planeados:**
- `BloodGroup.php` - Grupo sanguíneo (O+, O-, A+, A-, B+, B-, AB+, AB-)
- `Gender.php` - Género (M, F)
- `BirthType.php` - Tipo de parto (Normal, Cesarean)
- `MedicalStatus.php` - Estado médico (Positive, Negative, Not tested) para Chagas/Sífilis
- `ConsultationType.php` - Tipo de consulta (digital, manual)
- `ConsultationStatus.php` - Estado de consulta (draft, saved, finalized)
- `LicenseNumber.php` - Número de licencia médica con validación
- `PhoneNumber.php` - Número de teléfono con formato
- `Temperature.php` - Temperatura con validación de rango
- `Weight.php` - Peso con validación de rango
- `Height.php` - Altura con validación de rango
- `HeadCircumference.php` - Perímetro cefálico con validación

**Características:**
- Encapsulación de lógica de validación
- Conversión de tipos con casting automático en modelos
- Métodos helper para operaciones comunes
- Immutables por defecto
- Comparable y serializable

---

### 8. **Presentation Layer** (Capa de Presentación)
Interfaces de usuario y APIs.

**Ubicación:** `resources/views/`, `app/Http/Controllers/`

**Componentes:**
- Blade templates para vistas
- Livewire components para interactividad
- Form requests para validación
- Controllers para orquestar requests

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

### Unit Tests
- Factories en `tests/Unit/Factories/`
- Modelos en `tests/Unit/Models/` (próxima fase)
- Services en `tests/Unit/Services/` (próxima fase)

### Feature Tests
- Autenticación en `tests/Feature/Auth/`
- Settings en `tests/Feature/Settings/`
- APIs en `tests/Feature/Api/` (próxima fase)

### Test Database
- Usa `RefreshDatabase` trait
- Migraciones ejecutadas automáticamente
- Factories para setup de datos

## Phases de Implementación

### ✅ Phase 1: Foundation (Completada)
- [x] Modelos con UUIDs
- [x] Migrations
- [x] Factories
- [x] Tests básicos (41 tests passing)

### ⏳ Phase 2: Value Objects & Domain Logic
- [ ] Crear Value Objects
- [ ] Implementar casts en modelos
- [ ] Tests para Value Objects

### ⏳ Phase 3: Application Services
- [ ] Crear Services
- [ ] Crear DTOs
- [ ] Crear Contracts
- [ ] Tests para services

### ⏳ Phase 4: API Layer
- [ ] API Controllers
- [ ] API Tests
- [ ] API Documentation

### ⏳ Phase 5: Frontend Integration
- [ ] Livewire components
- [ ] Blade templates
- [ ] Form validation

## Stack Tecnológico

- **Framework:** Laravel 11.x
- **Database:** MySQL 8.0+
- **Testing:** Pest PHP
- **ORM:** Eloquent
- **Validation:** Laravel Form Requests
- **Frontend:** Blade + Livewire + Alpine.js
- **Styling:** Tailwind CSS

## Próximos Pasos

1. Implementar Phase 2 (Services, DTOs, Contracts)
2. Crear tests para la capa de aplicación
3. Generar documentación de APIs
4. Implementar comandos CLI
5. Desarrollar la capa Livewire

---

**Última actualización:** 22 de Febrero de 2026
**Versión:** 1.0.0
**Estado:** En desarrollo
