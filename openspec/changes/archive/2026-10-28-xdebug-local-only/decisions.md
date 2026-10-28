# Журнал рішень: xdebug лише для APP_ENV=local

## Проблема
`docker/php/Dockerfile` ставить і вмикає xdebug безумовно, тож образ для prod теж містить xdebug. Треба, щоб xdebug був лише в образі для `APP_ENV=local`.

## Дано
- Dockerfile виконує `pecl install xdebug && docker-php-ext-enable xdebug` і завжди копіює `xdebug.ini` — перевірено: `docker/php/Dockerfile`.
- Compose будує php з `build.args` (`HOST_UID`, `HOST_GID`) і передає `XDEBUG_MODE: ${XDEBUG_MODE:-debug}` — перевірено: `docker/docker-compose.yml`.
- `Makefile` запускає compose з `--env-file ../.env`, тож `APP_ENV` з кореневого `.env` доступний у compose — перевірено: `docker/Makefile`. `.env.example` має `APP_ENV=local`.
- Не обговорюється: значення `local` — з xdebug; будь-яке інше (зокрема `prod`) — без xdebug, без встановлення.

## Концепт рішення
**Обрано:** build-arg `APP_ENV` (default `local`); умовне встановлення xdebug і копіювання `xdebug.ini` в Dockerfile.
**Чий:** запропонував розробник (умова за `APP_ENV`); механізм build-arg обрано у воронці.
**Чому:** мінімальна зміна; образ prod збирається без xdebug.
**Відкинуто:**
- Multi-stage (`target:` у compose) — більша зміна структури.
- Xdebug завжди + `XDEBUG_MODE=off` на prod — суперечить «не встановлювати».
**ADR:** ні — локальна й оборотна зміна.

## Вектор
```
../.env (APP_ENV) ──► compose build.args APP_ENV ──► Dockerfile ARG APP_ENV
                                                        ├─ = local ─► pecl install + enable xdebug, COPY xdebug.ini
                                                        └─ інакше  ─► xdebug не ставиться, ini видаляється з образу
```

## Деталі
### Значення APP_ENV
**Рішення:** рівно `local` вмикає xdebug; будь-яке інше значення вважається prod.
### Конфігурація xdebug.ini
**Рішення:** `COPY` в Dockerfile безумовний, але в нескановану теку; для local файл переноситься в `conf.d/zz-xdebug.ini`, для інших значень видаляється — в образі prod немає ні xdebug, ні його конфігурації.
### XDEBUG_MODE у compose
**Рішення:** лишається як є (`${XDEBUG_MODE:-debug}`). На prod без xdebug змінна нешкідлива; змін у `environment` немає.
**Відкинуто:** дефолт за оточенням і окремий override — вихід за межі ТЗ.
### Дефолт build-arg
**Рішення:** `ARG APP_ENV=local` у Dockerfile і `APP_ENV: ${APP_ENV:-local}` у compose, щоб збирання без `.env` не ламалось.

## Відкриті питання
Немає.
