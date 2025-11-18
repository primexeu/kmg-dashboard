#!/bin/bash
set -euo pipefail

cd /app

ROLE="${CONTAINER_ROLE:-app}"
RUN_MIGRATIONS="${RUN_DB_MIGRATIONS:-true}"
QUEUE_TRIES="${QUEUE_TRIES:-3}"
QUEUE_TIMEOUT="${QUEUE_TIMEOUT:-90}"
PORT="${PORT:-10000}"

echo "[entrypoint] container role: ${ROLE}"
echo "[entrypoint] port: ${PORT}"

# Configure nginx to listen on the correct port
if [ "${ROLE}" != "worker" ]; then
  # Copy our custom nginx config
  cp /app/nginx.conf /opt/docker/etc/nginx/vhost.conf
  # Replace the port placeholder with actual port
  sed -i "s/listen 10000;/listen ${PORT};/" /opt/docker/etc/nginx/vhost.conf
fi

# Ensure storage symlink exists
php artisan storage:link >/dev/null 2>&1 || true

# Generate APP_KEY automatically if not provided
if [ -z "${APP_KEY:-}" ]; then
  echo "[entrypoint] APP_KEY not set, generating one temporarily"
  export APP_KEY="$(php artisan key:generate --show)"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${RUN_MIGRATIONS}" = "true" ]; then
  echo "[entrypoint] running database migrations"
  php artisan migrate --force
else
  echo "[entrypoint] skipping database migrations (RUN_DB_MIGRATIONS=${RUN_MIGRATIONS})"
fi

if [ "${ROLE}" = "worker" ]; then
  echo "[entrypoint] starting queue worker"
  exec php artisan queue:work --verbose --tries="${QUEUE_TRIES}" --timeout="${QUEUE_TIMEOUT}"
else
  echo "[entrypoint] starting nginx + php-fpm"
  exec /opt/docker/bin/entrypoint supervisord
fi
