FROM composer/composer:2-bin AS composer

# Wir bleiben bei 8.4 Bookworm, aber machen es sauberer
FROM dunglas/frankenphp:1-php8.4-bookworm AS base

# HIER IST DER FIX: Wir löschen den apt-Cache direkt nach der Installation!
RUN apt-get update && apt-get -y install --no-install-recommends \
    unzip \
    && rm -rf /var/lib/apt/lists/*

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
# Development (Dieses Image nutzt du lokal in Aachen)
#
FROM base AS dev
ARG USER_ID=10001
ARG GROUP_ID=10001
ARG USER_NAME=appuser
ARG GROUP_NAME=appuser

# Xdebug macht das Image größer, aber lokal ist uns das egal
RUN install-php-extensions xdebug

COPY --from=composer /composer /usr/bin/composer

RUN \
    groupadd --gid ${GROUP_ID} ${GROUP_NAME}; \
    useradd --gid ${GROUP_ID} --uid ${USER_ID} ${GROUP_NAME}; \
    setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
    chown -R ${USER_NAME}:${GROUP_NAME} /data/caddy && chown -R ${USER_NAME}:${GROUP_NAME} /config/caddy

USER ${USER_NAME}
WORKDIR /app

#
# Production (Dieses Image ist das kleine, das später online geht!)
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
