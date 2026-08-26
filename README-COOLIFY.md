# Despliegue en Coolify

La aplicación se despliega como **aplicación Docker individual** (build pack
`Dockerfile`) y la base de datos **MariaDB** vive como **recurso independiente**
en Coolify. Esta separación habilita las copias de seguridad automáticas de la
base de datos (no funcionan con bases embebidas en Compose).

## 1. Requisitos

- Una instancia de Coolify con un servidor configurado.
- El repositorio Git accesible desde Coolify.
- Un dominio apuntando a la dirección IP del servidor.
- Build pack **Dockerfile** (el `Dockerfile` está en la raíz del repo).

## 2. Crear la base de datos MariaDB

1. En el proyecto y ambiente de la clínica: **New Resource → Database → MariaDB**.
2. Desde la página del recurso, anota: **Internal Hostname**, puerto (`3306`),
   usuario y contraseña. Se usarán en las variables de la aplicación.

## 3. Variables de la aplicación

En la app **Environment Variables**:

```dotenv
APP_KEY=base64:CAMBIAR_POR_UNA_CLAVE_REAL
APP_URL=https://clinica.example.com
RUN_LARAVEL_SETUP=true
DB_CONNECTION=mariadb
DB_HOST=HOSTNAME_INTERNO_DE_LA_DB
DB_PORT=3306
DB_DATABASE=vitaltrack
DB_USERNAME=usuario_de_la_db
DB_PASSWORD=password_de_la_db
```

Genera `APP_KEY` con `php artisan key:generate --show`. **No cambies `APP_KEY`
después del primer despliegue** (invalida sesiones y datos cifrados).

`RUN_LARAVEL_SETUP=true` es obligatorio: el entrypoint de la imagen ejecuta las
migraciones y tareas de inicialización al arrancar el contenedor.

Correo SMTP (opcional):

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

## 4. Build pack y primer despliegue

1. Configuración de la app → Build Pack → **Dockerfile**.
2. Asigna el dominio a la app (puerto `80`) y activa HTTPS con redirección.
3. **Deploy**. El entrypoint ejecuta automáticamente:

```bash
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan storage:link --force
php artisan optimize
```

## 5. Seed inicial completo (una sola vez)

Las migraciones y los roles se cargan solos. Para el resto del catálogo, abre la
terminal del contenedor `app` y ejecuta:

```bash
php artisan db:seed --force
```

Crea: configuración de la clínica, roles/permisos, condiciones médicas, catálogo
de laboratorio, esquema PAI Bolivia, plantillas de recetas, datos OMS y los
usuarios por defecto (`admin@clinica.com`). **No crea pacientes de prueba en
producción.**

Nota: `migrate:fresh` está prohibido en producción (protección contra borrados
accidentales) y no hace falta: la base nueva se migra sola. El seeder completo es
idempotente.

## 6. Copias de seguridad

### Destino S3-compatible

Coolify guarda los backups en destinos S3-compatible. Dos opciones:

- **MinIO local** (recomendado para empezar): New Resource → Service → MinIO,
  con un volumen persistente. Anota las credenciales que genera.
- **Bucket externo**: Backblaze B2, AWS S3, Wasabi, etc.

### Backup programado de la base de datos

1. Recurso MariaDB → **Backups** → crear schedule (ej. semanal: cron `0 2 * * 1`).
2. Selecciona el destino y configura la **retención** (conservar las últimas N
   copias; Coolify borra las antiguas automáticamente).
3. Prueba con el botón **Backup** manual y verifica que el dump llegue al destino.

### Almacenamiento de archivos clínicos

El volumen de la app (`storage/`) contiene recetas, órdenes de laboratorio y
adjuntos clínicos: **respáldalo también**. Opción recomendada: Scheduled Task en
la app que comprima `storage/` hacia el mismo destino S3 (o copia manual
periódica del volumen).

## 7. Acceso manual on-demand (opcional)

Si querés inspeccionar o exportar la base manualmente:

- New Resource → Service → **phpMyAdmin**, conectado a la MariaDB.
- Dejalo **detenido** (STOPPED) por defecto; encendelo solo cuando lo necesites
  y volvelo a apagar.

## 8. Comprobaciones posteriores

```bash
curl --fail http://127.0.0.1/up            # endpoint de salud
php artisan migrate:status                 # migraciones aplicadas
```

Abre el dominio y verifica el acceso con `admin@clinica.com`.

## 9. Actualizaciones

1. Crea primero una copia de seguridad (botón Backup o el schedule).
2. Envía los cambios a la rama configurada → **Redeploy**.
3. Revisa los logs del contenedor `app` y verifica `/up`.

Las migraciones pendientes se ejecutan automáticamente antes de iniciar Apache.

## 10. Solución de problemas

### Falta `APP_KEY` / error de conexión a la base

- `APP_KEY`: genera una clave válida y no la cambies después.
- Conexión: verifica `DB_HOST` (hostname interno del recurso MariaDB), `DB_PORT`,
  `DB_USERNAME` y `DB_PASSWORD`. No uses `localhost` como host.

### Error de permisos en archivos

El entrypoint crea los directorios de `storage/` y corrige permisos al iniciar.
Si el volumen `app_storage` está montado, reinicia el servicio `app`.

## Documentación

- [Copias de seguridad de bases de datos](https://coolify.io/docs/databases/backups)
- [Aplicaciones Docker en Coolify](https://coolify.io/docs/applications)
- [Almacenamiento persistente en Coolify](https://coolify.io/docs/knowledge-base/persistent-storage)