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

## ⚡ Fase 3: UI Base y Componentes (sin bloquear dominio)

- [ ] **3.1 Tailwind/Design tokens**: configuración base del tema.
- [ ] **3.2 Layouts maestros**: Dashboard, Sidebar, Navbar.
- [ ] **3.3 Componentes atómicos**: Inputs, Buttons, Alerts, Modals.

---

## 🧱 Fase 4: MVP Contractual Completo (20+ clases/módulos)

> Ciclo obligatorio por CRUD: Migración + Modelo + Factory + Action/Service + Test Unitario + Test Feature.

### 4.1 Perfil Médico y Roles

- [ ] **4.1.1 CRUD Doctor** (User 1:1, matrícula, especialidad, estado).

### 4.2 Catálogos Clínicos (Prioridad temprana solicitada)

- [ ] **4.2.1 CRUD CategoriaLaboratorio** (Hematología, Orina, Imágenes, etc.).
- [ ] **4.2.2 CRUD CatalogoExamenLaboratorio** (exámenes individuales).
- [ ] **4.2.3 CRUD CatalogoMedicamento** (catálogo para recetas).
- [ ] **4.2.4 CRUD CatalogoVacuna** (PAI Bolivia).

### 4.3 Módulo Pacientes

- [ ] **4.3.1 CRUD Paciente** (filiación, nacimiento, antecedentes).

### 4.4 Módulo Plantillas (Ahorro de tiempo)

- [ ] **4.4.1 CRUD PlantillaReceta**.
- [ ] **4.4.2 CRUD ItemPlantillaReceta**.
- [ ] **4.4.3 CRUD PlantillaLaboratorio**.
- [ ] **4.4.4 CRUD ItemPlantillaLaboratorio**.

### 4.5 Flujo de Consulta (SOAP)

- [ ] **4.5.1 CRUD Consulta** (digital/manual, estado).
- [ ] **4.5.2 CRUD SignosVitales** (1:1 con consulta).
- [ ] **4.5.3 CRUD NotasSoap** (1:1 con consulta).
- [ ] **4.5.4 CRUD VacunaPaciente** (aplicaciones reales).

### 4.6 Resultados e Inmutabilidad (Snapshots)

- [ ] **4.6.1 CRUD Receta**.
- [ ] **4.6.2 CRUD DetalleReceta** (snapshot inmutable).
- [ ] **4.6.3 CRUD SolicitudLaboratorio**.
- [ ] **4.6.4 CRUD DetalleSolicitudLaboratorio** (snapshot + resultado).

### 4.7 Motor OMS (catálogo y datos)

- [ ] **4.7.1 CRUD OmsCatalogoGrafica** (boletas oficiales OMS).
- [ ] **4.7.2 CRUD OmsDatoGrafica** (LMS, Z-Score, percentiles).

### 4.8 Seguridad, policies y auditoría

- [ ] **4.8.1 Policies por rol** (Admin/Doctor y alcance por paciente).
- [ ] **4.8.2 Auditoría mínima** en acciones críticas de catálogos y consulta.

---

## 🧠 Fase 5: Lógica de Dominio (Services/Value Objects)

- [ ] **5.1 AgeValueObject**: cálculo de edad exacta (días/semanas/meses/años).
- [ ] **5.2 BloodGroupValueObject**: validación tipológica.
- [ ] **5.3 ZScoreService/ValueObject**: cálculo clínico OMS.
- [ ] **5.4 ConsultationSnapshotService**: copia inmutable de plantillas a transacciones.
- [ ] **5.5 GrowthChartService**: preparación de datasets para Chart.js.

---

## 📋 Fase 6: Integración de Flujo Clínico Completo (Livewire)

- [ ] **6.1 Gestión de pacientes**: listado, búsqueda, registro y edición.
- [ ] **6.2 Atención activa**: SOAP + signos + aplicación de plantillas.
- [ ] **6.3 Recetas y laboratorios**: creación, edición permitida por estado, cierre final.
- [ ] **6.4 Módulo híbrido**: subida y visualización de PDF/JPG históricos.

---

## 📈 Fase 7: Motor Gráfico OMS

- [ ] **7.1 Seeding masivo OMS**: importación CSV a `oms_datos_graficas`.
- [ ] **7.2 Gráficas Chart.js**: boletas, radio buttons y rangos.
- [ ] **7.3 Modo dual**: Médico (Z-Score) vs Padres (Percentil).
- [ ] **7.4 Pruebas de precisión**: coordenadas de pacientes de prueba.

---

## 🧪 Fase 8: Calidad, Seguridad y Cierre Técnico

- [ ] **8.1 Cobertura**: fortalecer suite de pruebas en módulos críticos.
- [ ] **8.2 Pruebas de autorización**: acceso correcto por rol/propietario.
- [ ] **8.3 Pruebas de regresión** del flujo completo de consulta.

---

## 🚀 Fase 9: Despliegue y Capacitación

- [ ] **9.1 Preparación productiva**: servidor, colas, storage, backup.
- [ ] **9.2 Carga inicial**: escaneos históricos comprometidos.
- [ ] **9.3 Capacitación**: uso operativo con la doctora.

---

## 📌 Orden recomendado de ejecución (sin recortar alcance)

1. **4.2 Catálogos clínicos** (lab + medicamentos) para habilitar flujos.
2. **4.3 + 4.5 Pacientes y Consulta** para atención real.
3. **4.4 + 4.6 Plantillas y snapshots** para ahorro de tiempo e inmutabilidad.
4. **4.7 OMS + Fase 7** para el componente gráfico completo.
5. **4.8 + Fase 8/9** para seguridad, cierre y salida a producción.

Este orden **no reduce** el MVP; solo optimiza la ejecución del alcance completo solicitado por cliente.