# 🏥 VitalTrack Pediátrico

Sistema web de gestión clínica diseñado específicamente para consultorios pediátricos. Una herramienta integral que optimiza el tiempo en consulta, mantiene la inmutabilidad del historial médico y proporciona gráficas de crecimiento OMS (Organización Mundial de la Salud).

## ✨ Características Principales

- **Gestión de Pacientes**: Registro completo, ficha con datos incompletos/completos, cálculo automático de edad (años/meses/días) y tamizaje neonatal ("prueba del talón")
- **Consultas Digitales**: Sistema híbrido que soporta tanto consultas digitales (SOAP) como historial en PDF/imagen escaneado
- **Gráficas OMS**: Motor visual de crecimiento (talla/edad, peso/edad, peso/talla, perímetro cefálico) con Modo Médico (Z-Score) y Modo Padres (Percentiles), tolerante a pacientes fuera del rango 0-60 meses
- **Inmutabilidad Histórica**: Las transacciones (recetas y laboratorios) se copian como snapshots para protección legal; auditoría de cambios (`audit_logs`) en entidades críticas
- **Automatización de Recetas y Laboratorios**: Plantillas precargadas (combos) con un solo clic, resultados de laboratorio por parámetro con adjuntos (imágenes/PDF)
- **Documentos Clínicos Imprimibles**: Recetas y órdenes de laboratorio en PDF con membrete configurable de la clínica (logo, dirección, WhatsApp)
- **Módulo PAI**: Esquema Nacional de Vacunación de Bolivia, con seguimiento de aplicadas/pendientes
- **Control de Roles**: Admin, Doctor, Enfermera, Secretaria, Técnico (policies por rol y alcance)

## 🛠️ Stack Tecnológico

- **Backend**: Laravel 12 (PHP 8.4+)
- **Base de Datos**: PostgreSQL 17
- **Autenticación**: Laravel Fortify (incluye 2FA) + Sanctum
- **Autorización**: Spatie Laravel Permission
- **Frontend**: Blade + Livewire 4 (componentes Volt de archivo único) + Flux UI + Tailwind CSS 4
- **Gráficas**: Chart.js
- **Notificaciones UI**: SweetAlert2
- **PDF**: barryvdh/laravel-dompdf
- **Importación de datos**: PhpSpreadsheet (seeder de boletas OMS desde Excel)
- **Testing**: Pest PHP + Laravel Dusk (browser), PHPStan/Larastan nivel 8, Laravel Pint

## 📋 Requisitos Previos

- PHP 8.4+
- PostgreSQL 12+
- Composer 2.8+
- Node.js 18+ (para frontend assets)

## 🚀 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/usuario/ClinicaCumpito.git
cd ClinicaCumpito
```

### 2. Iniciar los servicios de desarrollo

Con Docker y Docker Compose instalados:

```bash
docker compose -f dev-docker-compose.yml up -d --wait
```

Esto inicia PostgreSQL en `127.0.0.1:5432` con la configuración incluida en
`.env.example`.

### 3. Instalar la aplicación

```bash
composer setup
```

El comando instala las dependencias de PHP y Node, configura Laravel, ejecuta
las migraciones y compila los assets.

Para detener PostgreSQL:

```bash
docker compose -f dev-docker-compose.yml down
```

Para borrar también los datos locales de PostgreSQL:

```bash
docker compose -f dev-docker-compose.yml down --volumes
```

La configuración de conexión local es:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=vitaltrack
DB_USERNAME=root
DB_PASSWORD=
```

## 🏃 Ejecución

### Servidor Local

```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`

### Compilar Assets

```bash
npm run dev    # Desarrollo con watch
npm run build  # Producción
```

## 🧪 Testing

Ejecutar todos los tests:

```bash
php artisan test
```

Con cobertura:

```bash
php artisan test --coverage
```

## 📚 Estructura del Proyecto

```
├── app/                    # Código de la aplicación
├── bootstrap/              # Inicialización de Laravel
├── config/                 # Archivos de configuración
├── database/               # Migraciones y seeders
├── public/                 # Assets públicos
├── resources/              # Vistas Blade y componentes Livewire
├── routes/                 # Definición de rutas
├── tests/                  # Tests Pest
└── storage/                # Archivos generados
```

## 🔧 Configuración

### Zona Horaria

La aplicación está configurada para **America/La_Paz** (GMT-4)

### Idioma

La interfaz está completamente en **Español** (es_ES)

## 📖 Estado del Proyecto

El MVP contractual (Fases 1-7 del roadmap: cimientos, catálogos, pacientes, consultas SOAP,
plantillas, snapshots inmutables, motor OMS y flujo clínico Livewire) está completo y en
producción. El proyecto ya tuvo demo con la clienta y actualmente está en una ronda de
correcciones y mejoras post-demo (documentos imprimibles, resultados de laboratorio por
parámetro con adjuntos, configuración de la clínica, etc.).

Ver [`Roadmap de Desarrollo.md`](Roadmap%20de%20Desarrollo.md) para el detalle fase por fase
y [`AGENTS.md`](AGENTS.md) para el estado técnico vigente (stack verificado, comandos, tests).

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
2. Commit con mensajes convencionales
3. Push a la rama (`git push origin feature/AmazingFeature`)
4. Abre un Pull Request

## 📝 Convenciones de Commit

Este proyecto utiliza **Conventional Commits** en español:

```
feat(pacientes): agregar formulario de registro
fix(consultas): corregir validación de fecha
docs(readme): actualizar instrucciones de instalación
test(grafica): agregar tests para OMS
refactor(auth): mejorar lógica de autorización
```

## 📄 Licencia

Este proyecto está bajo licencia MIT. Ver archivo LICENSE para más detalles.

## ✉️ Contacto

Para preguntas o sugerencias, contactar al equipo de desarrollo.

---

**Última actualización**: 10 de agosto de 2026
