# AGENTS.md — Clínica Cumpito / VitalTrack Pediátrico

Sistema de gestión clínica pediátrica (Bolivia). **MVP ya presentado a la clienta**; la ronda de correcciones post-demo de agosto (10 observaciones) fue completada y desde entonces se sumaron ~50 commits de mejoras (documentos PDF, resultados de laboratorio por parámetro, configuración de marca de la clínica, dashboard con métricas reales) → ver "Estado actual" más abajo.

## Stack (verificado en composer.json / package.json)

- Laravel 12 + PHP 8.4 · Livewire 4 + Volt · Flux UI · Tailwind CSS 4 · Chart.js 4
- PostgreSQL 17 en desarrollo y Dusk · tests PHP en SQLite `:memory:` (phpunit.xml)
- Pest 4 · Laravel Dusk 8 · Pint (preset laravel) · PHPStan/Larastan **nivel 8** (`app/ config/ database/`)
- `barryvdh/laravel-dompdf` (recetas/órdenes de laboratorio en PDF) · `phpoffice/phpspreadsheet` (seeder OMS) · SweetAlert2 (reemplazó notificaciones nativas del navegador)

## Comandos

```bash
# Setup inicial (PostgreSQL en 127.0.0.1:5432, DB vitaltrack / user root / sin password)
docker compose -f dev-docker-compose.yml up -d --wait
composer setup            # install + .env + key + migrate + npm install + build

composer dev              # server (busca puerto libre desde 8000, scripts/serve.php) + queue + vite
php artisan test --parallel              # suite completa (~28s; baseline 10-ago-2026: 551 tests, 2279 assertions)
php artisan test --filter=NombreDelTest  # un solo test
composer lint             # pint --parallel (auto-fix)
./vendor/bin/phpstan analyse
npm run blade:fix         # prettier para .blade.php
npm run pre-commit        # gate completo: pint + phpstan + blade + tests
```

**Definition of Done** (Roadmap §6): implementación + migración/modelo/factory/servicio según corresponda + tests unit y feature + `pint` → `phpstan` → `php artisan test` en verde.

### Dusk (browser)

- `php artisan dusk` / `php artisan dusk --filter=test_07`
- `tests/DuskTestCase.php` levanta solo `php artisan serve --env=dusk.local` en **puerto 8000** (si el puerto ya responde, asume que la app corre) y lo mata al terminar.
- **`.env.dusk.local` está gitignored**: crearlo desde `.env` con `DB_DATABASE=vitaltrack_dusk`. Dusk corre contra PostgreSQL real servido, NO sqlite.
- Los 14 tests viven en un solo archivo: `tests/Browser/ClinicalWorkflowTest.php` (test_01…test_14).
- Quirk: `->type()` falla en `input[type=date]` tras re-render Livewire → usar `nativeInputValueSetter` (JS) + `dispatchEvent('input'/'change')`.

## Gotchas de entorno

- **`livewire/flux` es paquete Composer privado**: `composer install` requiere `composer config http-basic.composer.fluxui.dev <user> <key>`. CI usa secrets `FLUX_USERNAME`/`FLUX_LICENSE_KEY`. Si install falla en flux, es autenticación.
- CI (`.github/workflows/`): `lint.yml` corre `composer lint` y `tests.yml` la suite, en push/PR a develop/main/master/workos.
- Los Excel OMS fuente están en git: `resources/data who/`. `WhoDataSeeder` los importa a `oms_catalogo_graficas` + `oms_datos_graficas` (idempotente vía `updateOrCreate`).
- Seeders clave: `RolesAndPermissionsSeeder`, `DefaultUsersSeeder` (admin@ / doctor@ / doctora@ / tecnico@clinica.com), `VaccineCatalogSeeder` (PAI Bolivia), `LaboratoryCatalogSeeder`, `WhoDataSeeder`.
- `OmsDatoGrafica` declara `$table` explícito porque Laravel infiere mal el plural (`oms_dato_graficas`). Cuidado al crear modelos con nombres compuestos.
- El `Controller` base lleva `AuthorizesRequests` explícito (Laravel 12 ya no lo incluye).

## Arquitectura (lo no obvio)

- **Componentes Volt single-file**: páginas en `resources/views/pages/**/⚡nombre.blade.php`, embebidos en `resources/views/components/⚡nombre.blade.php`. El `⚡` es literal en el filename. Rutas con `Route::livewire('...', 'pages::pacientes.vacunas')` en `routes/web.php`.
- **Livewire 4**: NO pasar `Collection`s como props a Volt (Livewire asigna props antes de `mount()` → TypeError). Cargar datos desde DB dentro de `mount()`.
- Lógica de negocio en `app/Services` + `app/Contracts` + `app/DTOs` + `app/ValueObjects` (+ `app/Actions`); bindings DI en `AppServiceProvider`. Controladores y Volt solo coordinan.
- **Inmutabilidad clínica**: consulta `finalized` no se edita; plantillas se copian como snapshots (`ConsultationSnapshotService`); resultados de lab editables solo en ventana de 3 días.
- **Gráficas OMS**: el catálogo sembrado cubre **0–60 meses** (peso_edad, talla_edad, peso_talla, perimetro_cefalico × M/F). `GrowthChartService` calcula z-scores LMS; render en `resources/js/app.js` (`Alpine.data('omsChart')`, modos padres/médico). Pacientes fuera de ese rango se manejan como caso especial (commit `2e94667`), no se grafican en "0 meses" como antes.
- **Documentos clínicos en PDF**: `ClinicalDocumentService` + `ClinicalDocumentController` (rutas `documentos/recetas/{prescription}/{preview,pdf}` y `documentos/laboratorios/{laboratoryRequest}/{preview,pdf}`) generan receta y orden de laboratorio con `barryvdh/laravel-dompdf`, con membrete de la clínica (`ClinicSetting`: nombre/dirección/teléfono/whatsapp/logo — única tabla de dominio con id autoincremental, no UUID).
- **Resultados de laboratorio**: viven en `laboratory_item_results` (uno por parámetro, puede registrarse en una consulta posterior a la solicitud) + `laboratory_attachments` (imágenes/PDF, exactamente uno de `laboratory_request_id`/`laboratory_request_item_id` seteado). Las columnas de resultado que vivían directo en `laboratory_request_items` fueron eliminadas (migraciones `2026_03_20_100004`/`...005`).
- **Recetas**: `dose` unifica los antiguos campos "dosis" y "cantidad" (migración `2026_08_09_000002`, la doctora los confundía) y `administration_route` es nuevo (`2026_08_09_000001`).
- Modelos: UUID + SoftDeletes (excepto `clinic_settings`). TZ America/La_Paz. UI 100% en español.

## Convenciones del repo

- Mensajes, validaciones, labels y commits en **español**. Commits: `feat|fix|refactor(scope): descripción` (Conventional Commits).
- **Todo cambio de código exige tests**: ≥1 unit (`tests/Unit/`) + ≥1 feature (`tests/Feature/`). Regla no negociable del Roadmap. Tests con `RefreshDatabase` (sqlite memoria).
- No usar valores aleatorios de factory en tests (ej. `status` de Consultation con `randomElement`): hace tests no deterministas. Fijar valores explícitos.
- Frontend: dark mode agregando clases `dark:` sobre las clases base existentes (no reescribir secciones); colores por módulo (Pacientes teal, Consultas blue, Reportes purple); validar UX contra las 10 heurísticas de Nielsen (regla del proyecto).
- Ediciones mínimas y quirúrgicas; reutilizar estilos y patrones existentes antes de crear nuevos.

## Estado actual (10-ago-2026)

Las 10 observaciones de la clienta del demo de agosto (prueba del talón, bug de `wire:key` en
recetas, catálogo de vacunas, "moco" en heces, parámetros de laboratorio sin preselección, edad
en años/meses/días, fecha de aplicación de vacunas, gráficas OMS fuera de rango, flujo multi-día
de labs pendientes, feed de paciente deduplicado) **ya están resueltas**. Desde entonces el
desarrollo siguió en sprints (ver `git log`, mensajes `feat(...)`/`fix(...)` con sufijo
"Sprint N PX") agregando: documentos PDF imprimibles de recetas/laboratorios con membrete de la
clínica, resultados de laboratorio por parámetro con adjuntos, calendario mensual de controles
en la ficha del paciente, resumen de vacunas aplicadas/pendientes, dashboard con métricas reales
(reemplazó contadores estáticos y el banner de estado del sistema, ahora eliminado), y
SweetAlert2 en lugar de notificaciones nativas del navegador.

No hay una lista de prioridades pendientes documentada en este archivo; para el trabajo abierto,
revisar `git log` reciente, issues/tablero del proyecto si existen, y las fases marcadas
incompletas en `Roadmap de Desarrollo.md` (7.4 Pruebas de precisión OMS, 8.2/8.3 Autorización y
regresión, Fase 9 Despliegue y Capacitación).

## Documentos de referencia

- `Roadmap de Desarrollo.md` — estado por fases, DoD y política de tests (fuente de verdad del proceso).
- `Biblia del Proyecto.md` — spec de dominio clínico (documento de visión, no siempre refleja el estado exacto del código).
- `ARCHITECTURE.md` — visión hexagonal, actualizada a Laravel 12/PostgreSQL.
- `dbdiagram.md` — esquema real de base de datos, generado desde las migraciones.
- `README-COOLIFY.md` + `Dockerfile` / `compose.yaml` / `nixpacks.toml` — despliegue.

---

**Última actualización**: 10 de agosto de 2026 · Suite verificada: 551 tests PHP pasando (2279 assertions)
