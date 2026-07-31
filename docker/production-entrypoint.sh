#!/bin/sh
set -eu

mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data storage

if [ "${RUN_LARAVEL_SETUP:-false}" = "true" ]; then
    php artisan migrate --force
    php artisan db:seed --class=RolesAndPermissionsSeeder --force
    php artisan storage:link --force
    php artisan optimize
fi

exec "$@"
