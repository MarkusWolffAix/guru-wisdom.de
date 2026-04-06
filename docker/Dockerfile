FROM composer/composer:2-bin AS composer

# Switch to Alpine Linux base image to drastically reduce the final image size
FROM dunglas/frankenphp:1-php8.4-alpine AS base

# Alpine uses 'apk' instead of 'apt'. '--no-cache' prevents storing package index files, keeping the image slim.
RUN apk add --no-cache unzip

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

# Create a non-root user and grant necessary capabilities (Alpine specific syntax)
RUN apk add --no-cache libcap && \
    addgroup -g ${GROUP_ID} ${GROUP_NAME} && \
    adduser -D -u ${USER_ID} -G ${GROUP_NAME} ${USER_NAME} && \
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
