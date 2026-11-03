FROM php:8.5-fpm

ARG HOST_UID=1000
ARG HOST_GID=1000
ARG APP_ENV=local

COPY xdebug.ini /usr/local/etc/php/xdebug.ini

# Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session,
# Tokenizer, XML are already compiled into the official image; pdo_mysql, intl (Filament) and zip are added.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev \
    && docker-php-ext-install pdo_mysql intl zip \
    && if [ "$APP_ENV" = "local" ]; then \
        pecl install xdebug \
        && docker-php-ext-enable xdebug \
        && mv /usr/local/etc/php/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini; \
    else \
        rm /usr/local/etc/php/xdebug.ini; \
    fi \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# supercronic runs the crontab in the cron service (docker-compose.yml); the sha256 pins the release.
ARG SUPERCRONIC_VERSION=v0.2.51
ARG SUPERCRONIC_SHA256=cc232108374e8e2b537045f2d5eaa1229f619db314c3ba6a14d38b35dc0aa123
ADD --chmod=755 --checksum=sha256:${SUPERCRONIC_SHA256} \
    https://github.com/aptible/supercronic/releases/download/${SUPERCRONIC_VERSION}/supercronic-linux-amd64 \
    /usr/local/bin/supercronic

RUN groupadd -g ${HOST_GID} app \
    && useradd -u ${HOST_UID} -g ${HOST_GID} -m -s /bin/bash app

USER app
WORKDIR /var/www/larafast
