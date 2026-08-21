# syntax=docker/dockerfile:1.4
FROM php:8.3-cli-alpine AS builder

# install build deps
RUN apk add --no-cache git gcc libc-dev make

# install bc
RUN apk add bc

# install postgres client for schema generation if needed
RUN apk add --no-cache postgresql-client

# get composer (bypass PEAR)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /opt/src

# copy project deps + vendor
COPY ./composer.* ./
RUN composer install \
  --no-interaction \
  --no-scripts \
  --no-autoloader \
  --optimize-autoloader \
  --classmap-authoritative

# copy source code
COPY --chown=www-data:www-data ./src ./src

# standalone CLI image
FROM php:8.3-cli-alpine

# install runtime deps + pdo_pgsql extension
RUN apk add --no-cache tzdata postgresql-libs postgresql-dev \
    && docker-php-ext-install pdo_pgsql \
    && apk del postgresql-dev

WORKDIR /var/www/html

# copy compiled app from builder
COPY --from=builder /opt/src/vendor ./vendor
COPY --from=builder /opt/src/src ./src

# expose nothing (CLI only)
EXPOSE 0

# user
RUN addgroup -S app && adduser -S app -G app
USER app