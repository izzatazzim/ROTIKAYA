# syntax=docker/dockerfile:1
# ---------------------------------------------------------------------------
# Rotikaya Media Sales Tracking System — production image for Railway.
#
# Stage 1 builds the Vite front-end assets with Node.
# Stage 2 is the PHP runtime that actually serves the app.
# The container is database-agnostic: by default it uses SQLite stored on a
# Railway persistent volume mounted at /data (see docker/entrypoint.sh).
# ---------------------------------------------------------------------------

# ---- Stage 1: compile CSS/JS with Vite ------------------------------------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---- Stage 2: PHP application runtime --------------------------------------
FROM php:8.3-cli AS app

# install-php-extensions makes installing native extensions one line each.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Extensions required by Laravel (mbstring), DomPDF (gd) and the Excel export
# package / PhpSpreadsheet (zip, gd, bcmath). pdo_sqlite is the default DB
# driver; pdo_mysql is included so you can switch to a managed MySQL later
# without rebuilding logic. exif/intl satisfy PhpSpreadsheet's suggestions.
RUN install-php-extensions \
        gd \
        zip \
        mbstring \
        bcmath \
        exif \
        intl \
        pdo_sqlite \
        pdo_mysql

# Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy the full source, then the freshly built assets on top.
COPY . .
COPY --from=assets /app/public/build ./public/build

# Install PHP dependencies (production only) and tidy permissions.
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && chmod -R 775 storage bootstrap/cache

# Entrypoint prepares the database and starts the web server.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
