# syntax=docker/dockerfile:1.7

FROM php:8.4-fpm-alpine AS base

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    zip \
    unzip \
    && docker-php-ext-install -j"$(nproc)" intl mbstring opcache pdo pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci --prefer-offline --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM base AS vendor

# Токен снимает анонимный лимит GitHub API (основная причина 504 на zipball).
ARG GITHUB_TOKEN=""

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_CACHE_DIR=/composer-cache \
    COMPOSER_MAX_PARALLEL_HTTP=4 \
    COMPOSER_PROCESS_TIMEOUT=600

COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/composer-cache \
    if [ -n "$GITHUB_TOKEN" ]; then \
        composer config --global --auth github-oauth.github.com "$GITHUB_TOKEN"; \
    fi; \
    n=0; \
    until composer install --no-dev --no-interaction --no-progress --optimize-autoloader --no-scripts; do \
        n=$((n + 1)); \
        if [ "$n" -ge 3 ]; then echo "composer install failed after $n attempts" >&2; exit 1; fi; \
        echo "composer install failed, retry $n in 20s..." >&2; \
        sleep 20; \
    done

FROM base AS app

COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

ENV TBANK_CA_BUNDLE=/etc/ssl/certs/tbank-ca-bundle.pem

RUN cat /etc/ssl/certs/ca-certificates.crt docker/certs/russian-trusted-root-ca.crt > "$TBANK_CA_BUNDLE" \
    && composer dump-autoload --optimize \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["php-fpm"]

FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/html/public
COPY --from=assets /app/public/build /var/www/html/public/build
RUN rm -rf /var/www/html/public/storage \
    && ln -s /var/www/html/storage/app/public /var/www/html/public/storage
