# 🚀 VitalTrack: Roadmap de Desarrollo (Laravel 12 + PHP 8.4)

Este documento detalla las tareas necesarias para construir el sistema, priorizando:

- calidad de código,
- cumplimiento de principios SOLID,
- cobertura de pruebas.

---


## 📜 Criterios de Desarrollo Obligatorios (Aplican a Todas las Subtareas)

### 1) Arquitectura (Laravel 12 + DDD ligero)

- **Modelos Eloquent**: solo persistencia, relaciones, scopes, casts y accessors/mutators simples.
- **Lógica de negocio**: debe vivir en `app/Actions`, `app/Services` o `app/ValueObjects`.
- **Controladores / Livewire**: coordinan flujo; no deben contener reglas de negocio complejas.
- **Regla de carpetas**: cada nueva pieza debe respetar la estructura actual del proyecto (no crear capas paralelas sin justificar).

### 2) Principios SOLID (Criterio de aceptación)

- **S (Single Responsibility)**: una clase = una razón de cambio.
- **O (Open/Closed)**: extender por composición/estrategias, evitar modificar comportamiento estable.
- **L (Liskov)**: contratos consistentes; no romper comportamiento esperado en implementaciones.
- **I (Interface Segregation)**: interfaces pequeñas y específicas por caso de uso.
- **D (Dependency Inversion)**: depender de contratos/abstracciones, no de concreciones acopladas.

### 3) Estilo y consistencia de código

- **PSR-12 + Laravel Pint** obligatorio antes de cerrar una tarea.
- **Nombres explícitos**: prohibidos nombres ambiguos (`data`, `temp`, `helper` genérico).
- **Métodos cortos**: preferir extracción de métodos y clases por intención.
- **Sin duplicación**: si se repite lógica en 2+ lugares, extraer componente reutilizable.
- **Blade/Tailwind**: sin estilos inline; usar componentes Blade para patrones repetidos.

### 4) Reglas específicas del dominio clínico

- **Inmutabilidad histórica**: una consulta cerrada no se edita; se versiona o se crea snapshot.
- **Datos sensibles**: validación estricta de entrada y políticas de acceso por rol.
- **Trazabilidad**: toda acción crítica debe poder auditarse (quién, cuándo, qué cambió).

### 5) Política de pruebas obligatoria (No negociable)

- **Toda implementación nueva** (modelo, acción, servicio, componente Livewire, endpoint o regla de dominio) debe incluir:
	- **mínimo 1 test unitario** del comportamiento principal,
	- **mínimo 1 test de integración** del flujo real donde se usa.
- **Toda modificación** de código existente debe actualizar sus tests unitarios y de integración afectados.
- **No se permite cerrar subtareas** con cobertura solo manual o pruebas "pendientes".
- **Objetivo de pruebas**: validar no solo que el código funcione aislado, sino que se integre correctamente con el resto del sistema.

### 6) Definición de Terminado (Definition of Done)

Una subtarea se considera **terminada** solo si cumple todo lo siguiente:

- [ ] Implementación funcional completa del alcance.
- [ ] Pruebas automatizadas creadas/actualizadas (`.test.php`) y pasando.
- [ ] Cada desarrollo nuevo incluye test unitario + test de integración obligatorios.
- [ ] `php artisan test` sin fallos en el scope afectado.
- [ ] `./vendor/bin/pint` aplicado sin cambios pendientes.
- [ ] Sin violaciones críticas de análisis estático (PHPStan en nivel definido por el proyecto).
- [ ] Documentación mínima actualizada (README, roadmap o notas técnicas si aplica).

### 7) Plantilla obligatoria para pedir subtareas al agente

Usar este bloque al crear una subtarea para forzar cumplimiento:

> **Restricciones obligatorias de implementación**
> - Respetar arquitectura actual (`Models` delgados, lógica en `Actions/Services/VO`).
> - Aplicar SOLID explícitamente y justificar decisiones de diseño.
> - No introducir estilos inline ni romper convención Blade/Tailwind.
> - Entregar con pruebas (`Pest`) y validación de formato (`Pint`).
> - Incluir siempre test unitario + test de integración por cada funcionalidad nueva.
> - No cerrar tarea si no cumple la Definition of Done del roadmap.

---

## 📅 Fase 1: Inicialización y Entorno

- [x] **1.1 Bootstrap del proyecto**: instalar Laravel 12 con PHP 8.4.
- [x] **1.2 Git Flow**: configurar repositorio y reglas de commits.
- [x] **1.3 Configuración de DB**: configurar MySQL/MariaDB y variables de entorno.
- [x] **1.4 CI/CD Setup**: configurar GitHub Actions (o similar) para ejecutar tests en cada push.


## 🛠 Fase 2: Librerías y Herramientas Base

- [x] **2.1 Autenticación**: instalar y configurar Laravel Breeze (Livewire Functional).
- [x] **2.2 Permisos**: instalar `spatie/laravel-permission` y crear roles base (Doctor, Admin).
- [x] **2.3 Herramientas de calidad**: instalar PHPStan (Nivel 8) y Pint para estilo de código.
- [x] **2.4 Testing engine**: configurar Pest PHP como motor principal de pruebas.



## 🎨 Fase 3: Diseño y UI Base (Tailwind CSS)

- [ ] **3.1 Configuración de Tailwind**: personalizar `tailwind.config.js` con paleta corporativa (Teal/Pink/Indigo).
- [ ] **3.2 Layouts maestros**: crear componentes Blade/Livewire para Dashboard, Sidebar y Navbar.
- [ ] **3.3 Componentes atómicos**: crear componentes reutilizables (Inputs, Buttons, Modals, Alerts) con Alpine.js.

## ⚡ Fase 3.5: Prioridad actual — CRUDs de Catálogos Clínicos

> **Objetivo**: adelantar estos módulos para habilitar el flujo de recetas y laboratorios desde etapas tempranas.

- [ ] **3.5.1 Catálogo de recetas (medicamentos/plantillas)**:
	- Migración + modelo + factory del catálogo de medicamentos.
	- CRUD Livewire completo (listar, crear, editar, desactivar/activar).
	- Búsqueda reactiva y validaciones de unicidad por nombre/código.
	- Test unitario del modelo/reglas + test de integración del componente Livewire.

- [ ] **3.5.2 Catálogo de laboratorios (exámenes/plantillas)**:
	- Migración + modelo + factory del catálogo de exámenes de laboratorio.
	- CRUD Livewire completo (listar, crear, editar, desactivar/activar).
	- Clasificación por tipo de estudio y soporte para plantillas frecuentes.
	- Test unitario del modelo/reglas + test de integración del componente Livewire.

- [ ] **3.5.3 Seguridad y auditoría de catálogos**:
	- Policies/permisos por rol (Doctor/Admin) para cada acción de catálogo.
	- Trazabilidad mínima de cambios críticos (quién, cuándo, qué cambió).
	- Tests de integración de autorización (permitido/denegado).

## 🗄 Fase 4: Capa de Datos (Modelos, Migraciones y Factories)

> **Nota**: cada modelo debe crearse junto con su Factory y su test unitario de existencia.

- [ ] **4.1 Estructura médica**: migraciones y modelos para Doctor y User (relación 1:1).
- [ ] **4.2 Estructura paciente**: migración y modelo Paciente (campos de nacimiento y antecedentes).
- [ ] **4.3 Estructura clínica**: migraciones para Consulta, SignosVitales y NotasSoap.
- [ ] **4.4 Estructura de apoyo**: migraciones para Receta, SolicitudLaboratorio y sus detalles inmutables.
- [ ] **4.5 Catálogos**: migraciones para CatalogoVacunas y CatalogoExamenes.

## 🧠 Fase 5: Dominio y Lógica de Negocio (Value Objects & Services)

- [ ] **5.1 Value Objects (VO)**:
	- `AgeValueObject`: cálculo de edad precisa (días, semanas, meses, años) con `readonly classes` en PHP 8.4.
	- `BloodGroupValueObject`: validación de tipos de sangre.
	- `ZScoreValueObject`: lógica matemática para interpretación de datos OMS.

- [ ] **5.2 Services**:
	- `ConsultationSnapshotService`: copiar datos de plantillas a registros reales.
	- `GrowthChartService`: preparar arrays para Chart.js.

## 📋 Fase 6: CRUDs Reactivos (Livewire 3)

- [ ] **6.1 Gestión de pacientes**:
	- Listado con búsqueda AJAX (Livewire).
	- Formulario de registro con validación en tiempo real.
	- Tests de integración del componente Livewire (creación exitosa y errores de validación).

- [ ] **6.2 Gestión de doctores**: perfil del médico y configuración de matrícula.
- [ ] **6.3 Gestión de catálogos complementarios**: CRUDs para vacunas y otros catálogos no clínicos críticos.

## 🏥 Fase 7: Módulo de Consulta Médica (El Corazón)

- [ ] **7.1 Flujo de consulta**:
	- Registro de signos vitales.
	- Notas SOAP (Subjetivo, Objetivo, Análisis, Plan).
	- Módulo híbrido: subida de archivos (PDF/JPG) para historias antiguas.

- [ ] **7.2 Recetas y laboratorios**:
	- Uso de catálogos ya implementados en Fase 3.5 (recetas y laboratorios).
	- Buscador de medicamentos/exámenes.
	- Aplicación de plantillas (snapshots).
	- Generación de PDF para impresión.

## 📈 Fase 8: Módulo de Crecimiento OMS

- [ ] **8.1 Seeding masivo**: script para importar CSV de la OMS (Z-Scores y percentiles) a `oms_datos_graficas`.
- [ ] **8.2 Componente Livewire de gráficas**:
	- Integración con Chart.js.
	- Lógica de radio buttons para cambiar “boletas”.
	- Toggle Médico/Padres (Z-Score vs Percentil).

- [ ] **8.3 Pruebas de integración**: verificar que un paciente con datos específicos genere coordenadas correctas en la gráfica.

## 🧪 Fase 9: Calidad y Pruebas Finales

- [ ] **9.1 Cobertura de tests**: asegurar >80% de cobertura en modelos y controladores.
- [ ] **9.2 Pruebas de estrés**: validar rendimiento con carga masiva de datos OMS.
- [ ] **9.3 Auditoría de seguridad**: revisar policies para que cada doctor vea solo sus pacientes (o según configuración).

## 🚀 Fase 10: Despliegue (Bono de Escaneado)

- [ ] **10.1 Preparación de producción**: configuración de servidor (Forge, Vapor o VPS).
- [ ] **10.2 Migración de datos físicos**: carga de escaneos prometidos en el contrato.
- [ ] **10.3 Capacitación**: entrega de manual de usuario a la doctora.

---
