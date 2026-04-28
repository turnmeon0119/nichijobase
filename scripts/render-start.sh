#!/usr/bin/env sh
set -eu

mkdir -p /var/data
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

if [ "${DB_CONNECTION:-}" = "sqlite" ]; then
  db_path="${DB_DATABASE:-/var/data/database.sqlite}"
  mkdir -p "$(dirname "$db_path")"
  touch "$db_path"
fi

php artisan migrate --force

if [ "${RENDER_SEED_DATABASE:-false}" = "true" ]; then
  php artisan db:seed --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
