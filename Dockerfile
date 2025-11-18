# ---------------------------------------------
# Stage 1 — PHP dependencies
# ---------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app

RUN apk update && apk add --no-cache icu-dev git unzip

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress

COPY . .
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress

# ---------------------------------------------
# Stage 2 — Front-end assets
# ---------------------------------------------
FROM node:20 AS frontend
WORKDIR /app

COPY package.json package-lock.json vite.config.js postcss.config.js tailwind.config.js ./
RUN npm ci

COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------------------------------------------
# Final image — Nginx + PHP-FPM
# ---------------------------------------------
FROM webdevops/php-nginx:8.2

ENV APP_ENV=production \
    APP_DEBUG=false \
    WEB_DOCUMENT_ROOT=/app/public \
    WEB_DOCUMENT_INDEX=index.php

WORKDIR /app

# Ensure intl extension is available at runtime
RUN apk update && apk add --no-cache icu-dev

# Install Composer (runtime) for artisan commands that rely on it
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .
COPY --from=vendor /app/vendor /app/vendor
COPY --from=frontend /app/public/build /app/public/build

# Ensure directories are writable by the application user
RUN chown -R application:application storage bootstrap/cache

COPY render-entrypoint.sh /usr/local/bin/render-entrypoint
RUN chmod +x /usr/local/bin/render-entrypoint

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/render-entrypoint"]
