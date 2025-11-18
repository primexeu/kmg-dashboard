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

# Clear all caches to ensure fresh start (avoid database-dependent operations)
echo "[entrypoint] clearing file-based caches"
php artisan config:clear
php artisan route:clear
php artisan view:clear
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

# Rebuild package discovery and caches
echo "[entrypoint] rebuilding caches"
php artisan package:discover
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Wait for database to be ready
wait_for_db() {
  echo "[entrypoint] waiting for database connection..."
  local max_attempts=30
  local attempt=1
  
  while [ $attempt -le $max_attempts ]; do
    if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected';" >/dev/null 2>&1; then
      echo "[entrypoint] database connection established"
      return 0
    fi
    
    echo "[entrypoint] database not ready, attempt $attempt/$max_attempts"
    sleep 2
    attempt=$((attempt + 1))
  done
  
  echo "[entrypoint] failed to connect to database after $max_attempts attempts"
  return 1
}

if [ "${RUN_MIGRATIONS}" = "true" ]; then
  if wait_for_db; then
    echo "[entrypoint] running database migrations"
    php artisan migrate --force
    echo "[entrypoint] clearing database cache after migrations"
    php artisan cache:clear >/dev/null 2>&1 || true
  else
    echo "[entrypoint] skipping migrations due to database connection failure"
  fi
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
