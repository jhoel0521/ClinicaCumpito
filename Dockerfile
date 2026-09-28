# ---- Etapa 1: dependencias Composer (solo producción) ----
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-req=ext-gd \
    --no-scripts

# ---- Etapa 2: assets frontend ----
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline --no-audit

COPY --from=vendor /app/vendor ./vendor
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ---- Etapa 3: imagen final (Apache + PHP-FPM sobre Alpine) ----
FROM php:8.4-fpm-alpine

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Todas las dependencias en un solo RUN para reducir layers
RUN apk add --no-cache \
        icu-libs \
        libpng \
        freetype \
        libjpeg-turbo \
        libzip \
        zlib \
        apache2 \
        apache2-proxy \
        curl \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libpng-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && apk del .build-deps \
    && sed -i 's#/var/www/localhost/htdocs#/var/www/html/public#g' /etc/apache2/httpd.conf

COPY docker/php-production.ini /usr/local/etc/php/conf.d/99-production.ini
COPY docker/php-fpm-custom.conf /usr/local/etc/php-fpm.d/zz-custom.conf
COPY docker/apache-vhost.conf /etc/apache2/conf.d/vitaltrack.conf
# Internet móvil lento: compresión, caché de assets y timeouts largos de subida.
COPY docker/apache-low-bandwidth.conf /etc/apache2/conf.d/zz-low-bandwidth.conf
COPY --from=vendor /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build
COPY docker/production-entrypoint.sh /usr/local/bin/production-entrypoint

RUN mkdir -p \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && ln -s /var/www/html/storage/app/public /var/www/html/public/storage \
    && chmod 755 /usr/local/bin/production-entrypoint

EXPOSE 80

HEALTHCHECK --interval=15s --timeout=5s --start-period=90s --retries=5 \
  CMD curl --fail --silent --show-error http://127.0.0.1/up || exit 1

ENTRYPOINT ["production-entrypoint"]
CMD ["httpd", "-DFOREGROUND"]