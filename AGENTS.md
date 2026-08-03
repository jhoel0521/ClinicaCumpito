# AGENTS.md — Clínica Cumpito / VitalTrack Pediátrico

Sistema de gestión clínica pediátrica (Bolivia). **MVP ya presentado a la clienta**; estamos en correcciones post-demo → ver "Prioridades actuales".

## Stack (verificado en composer.json / package.json)

- Laravel 12 + PHP 8.4 · Livewire 4 + Volt · Flux UI · Tailwind CSS 4 · Chart.js 4
- PostgreSQL en desarrollo y Dusk · tests PHP en SQLite `:memory:` (phpunit.xml)
- Pest 4 · Laravel Dusk 8 · Pint (preset laravel) · PHPStan/Larastan **nivel 8** (`app/ config/ database/`)

## Comandos

```bash
# Setup inicial (PostgreSQL en 127.0.0.1:5432, DB vitaltrack / user root / sin password)
docker compose -f dev-docker-compose.yml up -d --wait
composer setup            # install + .env + key + migrate + npm install + build

composer dev              # server (busca puerto libre desde 8000, scripts/serve.php) + queue + vite
php artisan test --parallel              # suite completa (~45s; baseline ago-2026: 414 tests)
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
- **Gráficas OMS**: el catálogo sembrado cubre solo **0–60 meses** (peso_edad, talla_edad, peso_talla, perimetro_cefalico × M/F). `GrowthChartService` calcula z-scores LMS; render en `resources/js/app.js` (`Alpine.data('omsChart')`, modos padres/médico). Un paciente >5 años cae fuera de rango → ver obs #8.
- Modelos: UUID + SoftDeletes. TZ America/La_Paz. UI 100% en español.

## Convenciones del repo

- Mensajes, validaciones, labels y commits en **español**. Commits: `feat|fix|refactor(scope): descripción` (Conventional Commits).
- **Todo cambio de código exige tests**: ≥1 unit (`tests/Unit/`) + ≥1 feature (`tests/Feature/`). Regla no negociable del Roadmap. Tests con `RefreshDatabase` (sqlite memoria).
- No usar valores aleatorios de factory en tests (ej. `status` de Consultation con `randomElement`): hace tests no deterministas. Fijar valores explícitos.
- Frontend: dark mode agregando clases `dark:` sobre las clases base existentes (no reescribir secciones); colores por módulo (Pacientes teal, Consultas blue, Reportes purple); validar UX contra las 10 heurísticas de Nielsen (regla del proyecto).
- Ediciones mínimas y quirúrgicas; reutilizar estilos y patrones existentes antes de crear nuevos.

## Prioridades actuales — obs de la clienta post-demo (ago-2026)

Mapeo de las observaciones a archivos probables:

1. **Nueva consulta**: agregar campo "Prueba del talón al nacer" (realizó / no realizó). No existe nada de `talón` hoy → migración + formulario de consulta.
2. **Bug recetas**: al borrar una fila se borra otra → revisar keys/índices del `@foreach ($prescription['items'] as $iIndex => $item)` en `resources/views/components/⚡consultation-prescription.blade.php` (falta `wire:key` estable).
3. **Vacunas**: eliminar Hepatitis; BCG y demás con respuesta solo Sí/No; agregar Influenza 6m / 12m / anual (la anual con fecha de colocación) → `database/seeders/VaccineCatalogSeeder.php`, `⚡consultation-vaccines`, `pages/pacientes/⚡vacunas`.
4. **Laboratorio**: agregar opción "moco" en análisis de heces → `database/seeders/LaboratoryCatalogSeeder.php`.
5. **Solicitar laboratorio**: hoy los parámetros vienen todos seleccionados por defecto (`checked` en `selectorParameters`) → que inicie sin selección, en `⚡consultation-laboratory.blade.php`.
6. **Perfil paciente**: mostrar edad en años, meses y días (pacientes pequeños van seguido al médico) → `app/ValueObjects/Age.php::forDisplay()` y vistas de perfil.
7. **Vacunas**: permitir registrar **fecha de aplicación** al cargar esquema previo de un paciente nuevo (hoy solo marca tiene/no tiene) → `patient_vaccines` + componentes de vacunas.
8. **Gráficas OMS**: paciente de 8 años / 35 kg se grafica en "0 meses" → el catálogo solo llega a 60 meses; falta rango 5–13 años o guarda de fuera-de-rango en `GrowthChartService` / `⚡patient-oms-chart`.
9. **Flujo multi-día**: paciente vuelve otro día con labs pendientes → verificar que los pendientes aparezcan al reabrir el historial (`pages/pacientes/⚡laboratorios`; ya existe integración de órdenes previas pendientes en consulta).
10. **Resumen de paciente saturado**: el feed lista cada ítem de resultado como línea (se ve "Coprológico" ×7, "Hemograma" ×10). Mostrar solo exámenes solicitados, deduplicados, con estado → `pages/pacientes/⚡historia-feed.blade.php` + `tests/Feature/PatientHistoryFeedTest.php`.

## Documentos de referencia

- `Roadmap de Desarrollo.md` — estado por fases, DoD y política de tests (fuente de verdad del proceso).
- `Biblia del Proyecto.md` — spec de dominio clínico.
- `ARCHITECTURE.md` — visión hexagonal (parcialmente desactualizada: dice Laravel 11/MySQL; lo vigente es este archivo).
- `README-COOLIFY.md` + `Dockerfile` / `compose.yaml` / `nixpacks.toml` — despliegue.

---

**Última actualización**: 2 de agosto de 2026 · Suite verificada: 414 tests PHP pasando (1789 assertions)
