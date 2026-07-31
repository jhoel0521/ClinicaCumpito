# Despliegue en Coolify

Esta aplicación se despliega como un stack Docker Compose de producción. Incluye:

- Aplicación Laravel con PHP 8.4 y Apache.
- PostgreSQL 17.
- Worker para las colas de Laravel.
- Scheduler para las tareas programadas.
- Volúmenes persistentes para la base de datos y los archivos clínicos.

Ningún puerto se publica directamente en el servidor. Solo el servicio `app` debe
recibir un dominio mediante el proxy de Coolify.

## 1. Requisitos

- Una instancia de Coolify con un servidor configurado.
- El repositorio Git accesible desde Coolify.
- Un dominio apuntando a la dirección IP del servidor.
- Docker Compose seleccionado como método de despliegue.

El archivo principal del despliegue es:

```text
compose.yaml
```

## 2. Crear el recurso en Coolify

1. Entra al proyecto y ambiente donde se desplegará la clínica.
2. Selecciona **New Resource**.
3. Selecciona **Docker Compose**.
4. Conecta el repositorio Git.
5. Selecciona la rama de producción.
6. Indica `compose.yaml` como archivo Docker Compose.
7. No habilites **Raw Compose Deployment**.
8. No habilites **Connect to Predefined Network**.
9. Guarda la configuración sin desplegar todavía.

Coolify creará automáticamente una red privada para el stack. Los servicios se
comunican mediante sus nombres internos:

```text
app -> postgres:5432
queue -> postgres:5432
scheduler -> postgres:5432
```

## 3. Variables obligatorias

Configura estas variables en la sección **Environment Variables** de Coolify:

```dotenv
APP_KEY=base64:CAMBIAR_POR_UNA_CLAVE_REAL
APP_URL=https://clinica.example.com
```

Genera `APP_KEY` desde una instalación local del proyecto:

```bash
php artisan key:generate --show
```

No cambies `APP_KEY` después de poner la aplicación en producción. Cambiarla
invalidaría sesiones y datos cifrados previamente.

El Compose utiliza la variable mágica:

```dotenv
SERVICE_PASSWORD_64_POSTGRES
```

Coolify genera esta contraseña automáticamente y entrega el mismo valor a
Laravel y PostgreSQL. No es necesario crearla manualmente.

## 4. Variables recomendadas

Los siguientes valores tienen defaults seguros, pero pueden configurarse desde
Coolify:

```dotenv
APP_NAME="VitalTrack Pediátrico"
DB_DATABASE=vitaltrack
DB_USERNAME=vitaltrack
LOG_LEVEL=error
```

Para correo SMTP:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=usuario
MAIL_PASSWORD=contraseña
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="VitalTrack Pediátrico"
```

Si todavía no existe un proveedor SMTP, conserva `MAIL_MAILER=log`.

## 5. Configurar el dominio

Asigna el dominio únicamente al servicio `app`:

```text
Servicio: app
Dominio:  https://clinica.example.com
Puerto:   80
```

No asignes dominios a estos servicios:

- `postgres`
- `queue`
- `scheduler`

Tampoco agregues `ports:` al Compose. Coolify puede alcanzar el puerto interno
del servicio mediante su proxy sin exponerlo directamente en el servidor.

Activa HTTPS y la redirección de HTTP a HTTPS desde Coolify.

## 6. Almacenamiento persistente

El stack declara dos volúmenes:

```text
postgres_data  -> Base de datos PostgreSQL
app_storage    -> Archivos privados, públicos y documentos clínicos
```

No elimines estos volúmenes durante una actualización. La opción de borrar
volúmenes destruye permanentemente los datos persistentes.

Configura copias de seguridad periódicas de PostgreSQL desde Coolify y respalda
también el volumen `app_storage`.

## 7. Primer despliegue

Después de configurar las variables y el dominio:

1. Pulsa **Deploy**.
2. Espera a que `postgres` aparezca como saludable.
3. El servicio `app` ejecutará automáticamente:

```bash
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan storage:link --force
php artisan optimize
```

4. Cuando `app` esté saludable, Coolify iniciará `queue` y `scheduler`.
5. Abre el dominio configurado y verifica la pantalla de acceso.

El seeder es idempotente: crea los roles faltantes sin duplicar los existentes.

## 8. Comprobaciones posteriores

Los cuatro servicios deben estar ejecutándose:

```text
app        healthy
postgres   healthy
queue      healthy
scheduler  running
```

Desde la terminal del servicio `app` puedes revisar las migraciones:

```bash
php artisan migrate:status
```

Comprueba el endpoint de salud:

```bash
curl --fail http://127.0.0.1/up
```

Revisa que el worker esté procesando la cola:

```bash
php artisan queue:monitor default --max=100
```

## 9. Actualizaciones

Para desplegar una nueva versión:

1. Envía los cambios a la rama configurada en Coolify.
2. Crea primero una copia de seguridad de PostgreSQL.
3. Pulsa **Redeploy**.
4. Revisa los logs del servicio `app`.
5. Verifica `/up` y el acceso a la aplicación.

Las migraciones pendientes se ejecutan automáticamente antes de iniciar Apache.
El worker se reinicia con la misma imagen de la aplicación.

## 10. Solución de problemas

### Falta `APP_KEY`

Coolify impedirá el despliegue porque es una variable obligatoria. Genera una
clave válida con:

```bash
php artisan key:generate --show
```

### La aplicación no conecta con PostgreSQL

Comprueba que:

```dotenv
DB_HOST=postgres
DB_PORT=5432
```

Estos valores ya están definidos en `compose.yaml`. No uses `localhost` como
host de base de datos.

### Error de permisos en archivos

Revisa que el volumen `app_storage` esté montado y reinicia el servicio `app`.
El entrypoint crea los directorios necesarios y corrige sus permisos al iniciar.

### La aplicación responde, pero los trabajos no se procesan

Revisa los logs del servicio `queue`. Su proceso esperado es:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600
```

### Las tareas programadas no se ejecutan

Revisa los logs del servicio `scheduler`. Su proceso esperado es:

```bash
php artisan schedule:work
```

## Documentación

- [Docker Compose en Coolify](https://coolify.io/docs/knowledge-base/docker/compose)
- [Variables de entorno en Coolify](https://coolify.io/docs/knowledge-base/environment-variables)
- [Almacenamiento persistente en Coolify](https://coolify.io/docs/knowledge-base/persistent-storage)
- [Copias de seguridad de bases de datos](https://coolify.io/docs/databases/backups)
