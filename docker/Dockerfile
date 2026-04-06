FROM composer/composer:2-bin AS composer

# Alt: FROM dunglas/frankenphp:1-php8.4-bookworm AS base
# Neu:
FROM dunglas/frankenphp:1-php8.4-bookworm AS base

# ... der Rest bleibt gleich, aber achte auf die Installation von unzip
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
    simplexml \
    pdo \
    pdo_mysql

ENV LC_ALL=C.UTF-8

#
# Development Stage
#
FROM base AS dev
ARG USER_ID=10001
ARG GROUP_ID=10001
ARG USER_NAME=appuser
ARG GROUP_NAME=appuser

# Xdebug is heavy, so we only install it in the dev stage
RUN install-php-extensions xdebug

COPY --from=composer /composer /usr/bin/composer

# Create a non-root user and grant necessary capabilities (Debian syntax)
RUN \
    groupadd --gid ${GROUP_ID} ${GROUP_NAME} && \
    useradd --gid ${GROUP_ID} --uid ${USER_ID} ${GROUP_NAME} && \
    setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp && \
    chown -R ${USER_NAME}:${GROUP_NAME} /data/caddy && \
    chown -R ${USER_NAME}:${GROUP_NAME} /config/caddy

USER ${USER_NAME}
WORKDIR /app

#
# Production Stage
#
FROM base AS prod-builder
COPY --from=composer /composer /usr/bin/composer
COPY .. /app
RUN --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-progress --no-interaction --classmap-authoritative && \
    rm composer.lock composer.json

FROM base AS prod
ENV APP_ENV=prod
COPY --from=prod-builder --chown=www-data:www-data /app /app
USER www-data
