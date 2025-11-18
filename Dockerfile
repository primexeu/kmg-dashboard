# ---------------------------------------------
# Stage 1 — PHP dependencies
# ---------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app

RUN apt-get update && apt-get install -y libicu-dev git unzip && docker-php-ext-install intl

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
FROM node:18-alpine AS frontend
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
    WEB_DOCUMENT_INDEX=index.php \
    WEB_PHP_SOCKET=127.0.0.1:9000

WORKDIR /app

# Ensure intl extension is available at runtime
RUN apt-get update && apt-get install -y libicu-dev && docker-php-ext-install intl

# Install Composer (runtime) for artisan commands that rely on it
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .
COPY --from=vendor /app/vendor /app/vendor
COPY --from=frontend /app/public/build /app/public/build

# Ensure directories are writable by the application user
RUN chown -R application:application storage bootstrap/cache

# Copy configuration files
COPY render-entrypoint.sh /usr/local/bin/render-entrypoint
COPY nginx.conf /app/nginx.conf
RUN chmod +x /usr/local/bin/render-entrypoint

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/render-entrypoint"]
