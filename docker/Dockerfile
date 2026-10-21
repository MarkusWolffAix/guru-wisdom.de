# ==========================================
# 1. Composer Stage
# ==========================================
FROM composer/composer:2-bin AS composer

# ==========================================
# 2. Base Stage
# ==========================================
FROM dunglas/frankenphp:1-php8.5-trixie AS base

# Suppress debconf warnings during apt-get install
ARG DEBIAN_FRONTEND=noninteractive

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions for Yii3
RUN install-php-extensions \
    opcache \
    mbstring \
    intl

ENV LC_ALL=C.UTF-8

# CVE-2026-109618 & CVE-2026-109619 Mitigation
# Placed in BASE so both Dev and Prod stages are protected
RUN { \
    echo 'session.upload_progress.enabled = 0'; \
    echo 'max_multipart_body_parts = 50'; \
  } > /usr/local/etc/php/conf.d/99-cve-mitigation.ini

# ==========================================
# 3. Development Stage
# ==========================================
FROM base AS dev

ARG USER_ID=10001
ARG GROUP_ID=10001
ARG USER_NAME=appuser
ARG GROUP_NAME=appuser

# Install Xdebug only in the dev stage
RUN install-php-extensions xdebug

COPY --from=composer /composer /usr/bin/composer

# Create non-root user, grant port capabilities, and prepare Caddy directories
RUN groupadd --gid ${GROUP_ID} ${GROUP_NAME} && \
    useradd --gid ${GROUP_ID} --uid ${USER_ID} ${GROUP_NAME} && \
    setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp && \
    mkdir -p /data/caddy /config/caddy && \
    chown -R ${USER_NAME}:${GROUP_NAME} /data /config

WORKDIR /app
USER ${USER_NAME}

# ==========================================
# 4. Production Builder Stage
# ==========================================
FROM base AS prod-builder

COPY --from=composer /composer /usr/bin/composer
WORKDIR /app
COPY . /app

# Install dependencies optimized for production
RUN --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-progress --no-interaction --classmap-authoritative

# ==========================================
# 5. Production Stage
# ==========================================
FROM base AS prod

ENV APP_ENV=prod
WORKDIR /app

# 1. Grant port binding capabilities to FrankenPHP
RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp

# 2. Create directories and set permissions
RUN mkdir -p /data/caddy /config/caddy /app/assets /app/public/assets /app/runtime && \
    chown -R www-data:www-data /data /config /app && \
    chmod -R 775 /app/runtime

# 3. Copy the built application from the builder stage with correct permissions
COPY --from=prod-builder --chown=www-data:www-data /app /app

# 4. Switch to the secure non-root user
USER www-data
