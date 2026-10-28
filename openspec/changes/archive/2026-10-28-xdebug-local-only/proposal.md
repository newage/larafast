# Proposal

## Why

`docker/php/Dockerfile` ставить і вмикає xdebug безумовно, тож образ для prod теж містить xdebug. Налагоджувач не повинен потрапляти в образ поза локальним оточенням.

## What Changes

- Dockerfile отримує `ARG APP_ENV` (default `local`); `pecl install xdebug`, `docker-php-ext-enable xdebug` і `COPY xdebug.ini` виконуються лише при `APP_ENV=local`.
- Будь-яке інше значення `APP_ENV` (зокрема `prod`) дає образ без xdebug.
- `docker/docker-compose.yml` передає `APP_ENV: ${APP_ENV:-local}` у `build.args` сервісу php.
- `XDEBUG_MODE` у compose лишається без змін.

## Capabilities

### New Capabilities
- `dev-environment`: поведінка Docker-образу php залежно від `APP_ENV` (наявність xdebug). Каталог `openspec/specs/dev-environment/` існує, але spec.md порожній (0 байт), тому тут дельта з Purpose.

### Modified Capabilities

## Impact

- `docker/php/Dockerfile`, `docker/docker-compose.yml`.
- Після зміни образ php потрібно перезбирати (`make build`) при перемиканні `APP_ENV`.
- Поза межами: `XDEBUG_MODE`, `xdebug.ini`, інші сервіси compose, прод-деплой.
