FROM php:8.5-fpm

ARG HOST_UID=1000
ARG HOST_GID=1000
ARG APP_ENV=local

COPY xdebug.ini /usr/local/etc/php/xdebug.ini

# Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session,
# Tokenizer, XML are already compiled into the official image; only pdo_mysql is added.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && docker-php-ext-install pdo_mysql \
    && if [ "$APP_ENV" = "local" ]; then \
        pecl install xdebug \
        && docker-php-ext-enable xdebug \
        && mv /usr/local/etc/php/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini; \
    else \
        rm /usr/local/etc/php/xdebug.ini; \
    fi \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN groupadd -g ${HOST_GID} app \
    && useradd -u ${HOST_UID} -g ${HOST_GID} -m -s /bin/bash app

USER app
WORKDIR /var/www/larafast
