#!/usr/bin/env sh
# ---------------------------------------------------------------------------
# Container startup for the Rotikaya Sales Tracking System on Railway.
# Runs on every boot. Designed to be idempotent and safe to re-run.
# ---------------------------------------------------------------------------
set -e

# Resolve the SQLite database location. DB_DATABASE should point at a file on
# the mounted persistent volume, e.g. /data/database.sqlite. If you switch to
# a managed MySQL/Postgres these steps simply create an unused empty file.
DB_FILE="${DB_DATABASE:-/data/database.sqlite}"

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_DIR="$(dirname "$DB_FILE")"
    mkdir -p "$DB_DIR"
    [ -f "$DB_FILE" ] || touch "$DB_FILE"
    SEED_MARKER="${DB_DIR}/.seeded"
else
    SEED_MARKER="/tmp/.seeded"
fi

# Make uploaded files / stored PDFs reachable under /storage.
php artisan storage:link 2>/dev/null || true

# Apply database migrations (idempotent — only pending ones run).
php artisan migrate --force

# Seed demo data exactly once. The marker lives on the persistent volume so a
# redeploy never duplicates the seeded users/clients/invoices.
if [ ! -f "$SEED_MARKER" ]; then
    echo "Seeding demo data (first boot)..."
    php artisan db:seed --force
    touch "$SEED_MARKER"
fi

# Cache framework config/routes/views for faster responses.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the web server on the port Railway provides.
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
