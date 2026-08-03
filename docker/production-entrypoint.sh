#!/bin/sh
set -eu

mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

# Solo corregir permisos en directorios vacíos (no recursivo en todo storage/)
chown -R www-data:www-data storage/framework storage/logs bootstrap/cache
# Permisos para storage/app (sin -R para no recorrer archivos clínicos)
chown www-data:www-data storage/app storage/app/public storage/app/private 2>/dev/null || true

if [ "${RUN_LARAVEL_SETUP:-false}" = "true" ]; then
    php artisan migrate --force
    php artisan db:seed --class=RolesAndPermissionsSeeder --force
    php artisan storage:link --force
    php artisan optimize
fi

exec "$@"
