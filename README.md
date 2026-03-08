# 🏥 VitalTrack Pediátrico

Sistema web de gestión clínica diseñado específicamente para consultorios pediátricos. Una herramienta integral que optimiza el tiempo en consulta, mantiene la inmutabilidad del historial médico y proporciona gráficas de crecimiento OMS (Organización Mundial de la Salud).

## ✨ Características Principales

- **Gestión de Pacientes**: Registro completo con cálculo automático de edad
- **Consultas Digitales**: Sistema híbrido que soporta tanto consultas digitales como historial en PDF
- **Gráficas OMS**: Motor visual de crecimiento con 50 boletas OMS (0-13 semanas, 0-6 meses, 0-5 años, 0-13 años)
- **Inmutabilidad Histórica**: Las transacciones se copian como snapshots para protección legal
- **Automatización de Recetas y Laboratorios**: Plantillas precargadas (combos) con un solo clic
- **Módulo PAI**: Esquema Nacional de Vacunación de Bolivia
- **Control de Roles**: Doctora, Enfermera, Secretaria

## 🛠️ Stack Tecnológico

- **Backend**: Laravel 12 (PHP 8.4+)
- **Base de Datos**: PostgreSQL
- **Autenticación**: Laravel Fortify + Sanctum
- **Autorización**: Spatie Laravel Permission
- **Frontend**: Blade + Livewire + Flux UI
- **Gráficas**: Chart.js
- **Testing**: Pest PHP

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

### 2. Instalar Dependencias PHP

```bash
composer install
```

### 3. Configurar Variables de Entorno

```bash
cp .env.example .env
php artisan key:generate
```

Actualiza `.env` con tus credenciales de PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=vitaltrack
DB_USERNAME=root
DB_PASSWORD=root
```

### 4. Crear Base de Datos

```bash
# Si PostgreSQL está corriendo localmente
psql -U postgres -c "CREATE USER root WITH PASSWORD 'root';"
psql -U postgres -c "ALTER USER root WITH SUPERUSER;"
psql -U postgres -c "CREATE DATABASE vitaltrack OWNER root;"
```

### 5. Ejecutar Migraciones

```bash
php artisan migrate:fresh --seed
```

### 6. Instalar Node Dependencies

```bash
npm install
npm run build
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

## 📖 Fases de Desarrollo

1. **FASE 1**: Cimientos y Autenticación ✅
2. **FASE 2**: Gestión de Pacientes y Consultas Básicas
3. **FASE 3**: Motor de Ahorro de Tiempo (Plantillas)
4. **FASE 4**: Módulo de Vacunas PAI
5. **FASE 5**: Motor de la OMS (Gráficas)
6. **FASE 6**: Pruebas y Entrega

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

**Última actualización**: 21 de febrero de 2026
