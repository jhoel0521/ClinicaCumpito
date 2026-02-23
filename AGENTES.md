# AGENTES.md - Reglas de Programación y Guías para IA

## 🎯 Principios Fundamentales

### 1. **NO reescribir código innecesariamente**
- **Aplica**: Solo edita las líneas que necesitan cambios
- **NO**: Reescribir archivos completos si solo necesitas agregar/modificar pequeñas secciones
- **Ejemplo**: 
  - ❌ Reescribir todo `dashboard.blade.php` para agregar `dark:` clases
  - ✅ Usar `replace_string_in_file` con contexto mínimo necesario
  - ✅ Aplicar clases en secciones específicas (encabezado, cards, etc.)

### 2. **Reutiliza estilos base existentes**
- Antes de crear nuevas reglas CSS, verifica qué estilos ya existen
- Usa Tailwind classes base: `text-gray-900` → agrega `dark:text-white`
- No reimplementes lo que ya funciona
- Mantén consistencia con patrones ya establecidos

### 3. **Cambios mínimos, máximo impacto**
- Usa `multi_replace_string_in_file` para múltiples ediciones pequeñas
- Agrupa cambios relacionados en una sola operación
- Preserva formateo, indentación y estructura original

## 📋 Estándares de Código

### Principios SOLID Obligatorios
**TODA** la arquitectura debe cumplir con SOLID:

- **S**ingle Responsibility: Cada clase/método una única responsabilidad
- **O**pen/Closed: Abierto para extensión, cerrado para modificación
- **L**iskov Substitution: Las subclases deben poder reemplazar a la clase base
- **I**nterface Segregation: Interfaces específicas, no genéricas
- **D**ependency Inversion: Depender de abstracciones, no implementaciones

**Ejemplos aplicados en el proyecto:**
- `PacienteService` (Single Responsibility): solo gestiona lógica de pacientes
- `PacienteDTO` (Interface Segregation): transferencia de datos específica
- `StorePacienteRequest` (Open/Closed): validación extensible sin modificar lógica

### Tailwind CSS - Dark Mode
```html
<!-- ✅ CORRECTO: Agrega dark: a clases existentes -->
<h1 class="text-gray-900 dark:text-white">Encabezado</h1>
<p class="text-gray-600 dark:text-gray-400">Párrafo</p>

<!-- ❌ INCORRECTO: Reescribir secciones completas -->
<!-- NO hagas esto cuando solo necesites agregar dark: -->
```

### Estructura de Cambios
```
1. Identifica EXACTAMENTE qué líneas cambiar
2. Aplica cambios MÍNIMOS necesarios
3. Verifica que funcional esté intacta
4. No alteres estilos base establecidos
```

## 🏗️ Arquitectura del Proyecto

### Stack confirmado
- **Framework**: Laravel 11 + Livewire
- **UI**: Flux components (sidebar, header, dropdown)
- **Styling**: Tailwind CSS con dark mode via `class="dark"` en HTML
- **Testing**: Pest PHP (164 tests base)
- **Localización**: Español

### Patrones establecidos
- Modelos con UUID y SoftDeletes
- Value Objects con validación encapsulada
- Service layer + DTOs
- FormRequest con mensajes en español
- Blade templates con `<x-layouts::app>`

## 🧪 Testing Requirements

**Baseline**: 164 tests passing
- Phase 1: 41 tests (Models, Migrations, Factories)
- Phase 2: 94 tests (Value Objects)
- Phase 3: 23 tests (Pacientes CRUD)

### REQUISITO OBLIGATORIO: Testing Unit + Integration

**TODO cambio de código DEBE incluir:**

1. **Unit Tests** - Testeen funcionalidad aislada
   - Métodos de Service layer
   - Value Objects (validación, conversión)
   - DTOs
   - Helpers, Traits
   - NO deben usar base datos
   - Ubicación: `tests/Unit/`

2. **Integration/Feature Tests** - Testeen flujos completos
   - Controladores (CRUD actions)
   - Rutas con middleware
   - Interacción Service + Model + Database
   - FormRequests con validación
   - Ubicación: `tests/Feature/`

3. **Coverage Mínimo**
   - Casos exitosos (happy path)
   - Casos fallidosos (validación, autenticación, errores)
   - Edge cases (límites, valores especiales)

**Ejemplo Pattern:**
```bash
# Feature Test: Valida controlador + rutas + middleware
phpunit tests/Feature/PacienteControllerTest.php

# Unit Test: Valida Service layer aislado
phpunit tests/Unit/Services/PacienteServiceTest.php
```

**Después de CADA cambio código**:
```bash
php artisan test
```
- ✅ Debe mostrar: `Tests: 164 passed` (o más si agrega tests)
- ❌ Si baja el número → hay regresión, **NO hacer commit**
- ⚠️ Si NO tiene tests nuevos → **INCOMPLETO**, agregar tests

## 📁 Estructura de Archivos Clave

```
app/
  Http/Controllers/PacienteController.php      (7 CRUD methods)
  Http/Requests/StorePacienteRequest.php       (Validación)
  Http/Requests/UpdatePacienteRequest.php      (Validación)
  Services/PacienteService.php                 (Lógica)
  Data/PacienteDTO.php                         (Data Transfer)
  Models/Patient.php                           (UUID, SoftDeletes)

resources/views/
  dashboard.blade.php                          (Main entry point)
  layouts/app/sidebar.blade.php                (Módulos: Pacientes)
  pacientes/
    index.blade.php                            (Lista con paginación)
    create.blade.php                           (Formulario - 3 secciones)
    show.blade.php                             (Dashboard paciente)
    edit.blade.php                             (Editar paciente)
```

## 🎨 UI/UX Standards

### Componentes Flux
```php
<!-- Header -->
<flux:sidebar.header></flux:sidebar.header>

<!-- Navegación -->
<flux:sidebar.nav>
  <flux:sidebar.group :heading="__('Módulos')">
    <flux:sidebar.item icon="users" :href="route('pacientes.index')">
      {{ __('Pacientes') }}
    </flux:sidebar.item>
  </flux:sidebar.group>
</flux:sidebar.nav>
```

### Colores por módulo
- **Pacientes**: Teal (teal-50 → teal-900)
- **Consultas**: Blue (blue-50 → blue-900)
- **Reportes**: Purple (purple-50 → purple-900)
- **Disabled**: Gray (gray-50 → gray-700)

### Dark Mode Palette
```
Light   Dark
------  -----
gray-50 → zinc-900
white   → zinc-800
gray-600 → gray-400
gray-900 → white
```

## ✍️ Validación y Mensajes

### Mensajes en ESPAÑOL
```php
// ✅ CORRECTO
'full_name.required' => 'El nombre completo es requerido',
'date_of_birth.date' => 'La fecha de nacimiento debe ser una fecha válida',

// ❌ INCORRECTO
'full_name.required' => 'Full name is required',
```

### Value Objects
- Siempre usan encapsulación con `->value()`
- En vistas: `{{ is_object($field) ? $field->value() : $field }}`

## 🔄 Fases de Desarrollo

### ✅ Completadas
- **Fase 1**: Models (41 tests)
- **Fase 2**: Value Objects (94 tests)
- **Fase 3**: Pacientes CRUD (164 tests)
- **Navigation**: Sidebar + Dashboard links

### ⏳ Próximas
- **Fase 4**: Consultas CRUD
- **Fase 5**: Reportes
- **Enhancements**: Dark/light mode toggle, exportación PDF

## 💾 Git Workflow

```bash
# Commit pattern
git commit -m "feat|fix|refactor(module): descripción en español"

# Ejemplos
git commit -m "feat(pacientes-crud): Implementar CRUD completo de pacientes"
git commit -m "fix(dashboard): Corregir estilos dark mode"
git commit -m "refactor(sidebar): Agregar módulos de navegación"
```

## 🚨 Checklist Pre-Commit

**⚠️ OBLIGATORIO: Formatear código**
```bash
php vendor/bin/pint
```
Este paso es **INELUDIBLE** antes de commit. Pint aplica estándares PSR-12 y las reglas del proyecto.

Antes de hacer commit:
- [ ] ¿He ejecutado `php vendor/bin/pint` para formatear?
- [ ] ¿He ejecutado `php artisan test`?
- [ ] ¿Todos los tests pasan (164+ pasando)?
- [ ] ¿Incluí tests unitarios para new logic?
- [ ] ¿Incluí tests de integración para cambios de UI/Controller?
- [ ] ¿SOLID principles se cumplen? (Single Responsibility, etc.)
- [ ] ¿He aplicado cambios mínimos sin reescribir innecesariamente?
- [ ] ¿Se mantiene la consistencia de estilos base?
- [ ] ¿Los mensajes están en español?
- [ ] ¿He verificado dark mode en toda la UI?

### Patrón de Testing por Tipo de Cambio

**🔵 Nuevo Feature/CRUD**
- [ ] Unit tests para Service (create, update, delete, find)
- [ ] Feature tests para Controller (index, create, store, show, edit, update, destroy)
- [ ] Tests de validación (FormRequests)
- [ ] Tests de autenticación/autorización
- Mínimo: 15-20 tests

**🟢 Fix en lógica existente**
- [ ] Unit test que demuestre el bug
- [ ] Fix el código
- [ ] Unit test pasa
- [ ] Verifica que tests relacionados siguen pasando

**🟠 Cambio de UI/Estilo**
- [ ] Verifica que los tests de Feature siguensiendo verdes
- [ ] Dark mode agregado correctamente (no breaks)
- [ ] Responsive en mobile/desktop

**🔴 Refactor**
- [ ] Todos los tests existentes deben pasar (100% same behavior)
- [ ] Aplicar SOLID principles
- [ ] Sin cambios funcionales

## 🔐 No Olvidar - Reglas Críticas

1. **FORMATEO OBLIGATORIO**: `php vendor/bin/pint` ANTES de cualquier commit
2. **SOLID siempre**: Single Responsibility, Open/Closed, Liskov, Interface Segregation, Dependency Inversion
3. **Testing obligatorio**: Unit + Integration tests PARA CADA feature/fix
4. **Usa `replace_string_in_file` con contexto**: Incluye 3-5 líneas antes/después
5. **Para múltiples cambios**: Usa `multi_replace_string_in_file`
6. **Siempre valida**: `php artisan test` al final (sin --no-coverage para ver cambios)
7. **Reutiliza**: Examina estilos existentes antes de crear nuevos
8. **Sin regresiones**: Los 164+ tests deben siempre pasar
9. **Documentación**: Actualiza AGENTES.md si hay nuevas reglas o patrones
10. **Mensajes en ESPAÑOL**: Toda validación, labels, mensajes flash
11. **Commit solo si**: Pint aplicado + Tests pasan + Cambios quirúrgicos + SOLID cumplido + Tests nuevos incluidos

---

**Última actualización**: 22 de febrero, 2026
**Versión**: 1.1 (Agregados: SOLID, Testing Unit+Integration, Checklist de testing)
