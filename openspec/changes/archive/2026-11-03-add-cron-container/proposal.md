# Proposal

## Why

`schedule:run` ніхто не запускає: у docker немає процесу, який щохвилини викликає планувальник Laravel (у `routes/console.php` вже є `cloudflare:refresh-ips`). Заплановані команди на проді не виконуються, а розклад для local і prod має відрізнятися.

## What Changes

- Новий сервіс `cron` у `docker/docker-compose.yml`, зібраний з того ж `docker/php/Dockerfile`, що й `php`.
- supercronic додається в образ php (з фіксованою версією і перевіркою контрольної суми).
- Розклад зберігається в нативних crontab-файлах `docker/crond/prod` і `docker/crond/local`; сервіс запускає supercronic на файлі `docker/crond/${APP_ENV:-local}`.
- `docker/crond/prod`: `*/5 * * * * php artisan schedule:run`.
- `docker/crond/local`: `* * * * * php artisan schedule:run` (припущення, див. `decisions.md`).
- Код застосунку не змінюється.

## Capabilities

### New Capabilities
- `cron-scheduler`: окремий cron-контейнер, що запускає `schedule:run` за розкладом, обраним за `APP_ENV`.

### Modified Capabilities
<!-- Немає: поведінка xdebug у `dev-environment` не змінюється. -->

## Impact

- `docker/docker-compose.yml` — новий сервіс `cron`.
- `docker/php/Dockerfile` — установка supercronic.
- `docker/crond/prod`, `docker/crond/local` — нові файли розкладу.
- Залежності: бінарник supercronic у образі php (спільному з php-fpm).
- Джерело рішень: `decisions.md` цієї зміни.
