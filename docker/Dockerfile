# ==========================================
# 1. Composer Stage
# ==========================================
FROM composer/composer:2-bin AS composer

# ==========================================
# 2. Base Stage
# ==========================================
FROM dunglas/frankenphp:1-php8.5-trixie AS base

# Install system dependencies (e.g. unzip for composer)
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions for Yii3
RUN install-php-extensions \
    opcache \
    mbstring \
    intl \
    dom \
    ctype \
    curl \
    phar \
    openssl \
    xml \
    xmlwriter \
    simplexml

ENV LC_ALL=C.UTF-8

# ==========================================
# 3. Development Stage
# ==========================================
FROM base AS dev

ARG USER_ID=10001
ARG GROUP_ID=10001
ARG USER_NAME=appuser
ARG GROUP_NAME=appuser

# Xdebug is heavy, so we only install it in the dev stage
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
    composer install --no-dev --no-progress --no-interaction --classmap-authoritative && \
    rm composer.lock composer.json

# ==========================================
# 5. Production Stage
# ==========================================
FROM base AS prod

ENV APP_ENV=prod
WORKDIR /app

# 1. Create all system and app directories BEFORE copying the code
RUN mkdir -p /data/caddy /config/caddy /app/assets /app/public/assets /app/runtime && \
    chown -R www-data:www-data /data /config /app && chown -R www-data:www-data /app/runtime \
    && chmod -R 775 /app/runtime

# 2. Copy the built application from the builder stage with correct permissions
COPY --from=prod-builder --chown=www-data:www-data /app /app

# 3. Switch to the secure non-root user
USER www-data