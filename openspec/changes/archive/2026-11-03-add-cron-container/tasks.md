# Tasks

**Межі зміни:** чіпаємо лише `docker/php/Dockerfile`, `docker/docker-compose.yml`, `docker/crond/prod`, `docker/crond/local`. Не чіпаємо: `app/`, `routes/`, `bootstrap/`, `docker/nginx/`, `docker/Makefile`, наявні сервіси compose (`php`, `nginx`, `mysql`, `node`).

## 1. Файли розкладу

- [x] 1.1 Створити `docker/crond/prod` з рядком `*/5 * * * * php artisan schedule:run` і коментарем про призначення. Перевірка: `supercronic -test docker/crond/prod` (після 2.1) завершується без помилок.
- [x] 1.2 Створити `docker/crond/local` з рядком `* * * * * php artisan schedule:run` і коментарем. Перевірка: `supercronic -test docker/crond/local` без помилок.

## 2. Образ

- [x] 2.1 У `docker/php/Dockerfile` додати завантаження supercronic: після блоку `RUN apt-get ...`, до `USER app`; фіксована версія й перевірка sha256 через `ARG`. Версію і sha256 взяти з релізів supercronic під архітектуру `linux-amd64`. Не змінювати блок xdebug. Перевірка: `make build` проходить, `docker compose run --rm cron supercronic -version` друкує версію.

## 3. Сервіс compose

- [x] 3.1 У `docker/docker-compose.yml` додати сервіс `cron` за зразком `php`: той самий `build` (context, args), том `..:/var/www/larafast`, `depends_on: mysql`, `command: supercronic /var/www/larafast/docker/crond/${APP_ENV:-local}`. Не додавати `ports`. Перевірка: `docker compose --env-file ../.env config` показує сервіс `cron` з потрібною командою.
- [x] 3.2 Перевірити з `APP_ENV=local`: `make build && make up`, у `docker logs` сервісу `cron` з'являється запуск `schedule:run` протягом хвилини.
- [x] 3.3 Перевірити з `APP_ENV=prod` (тимчасово в `.env`, потім повернути `local`): команда сервісу вказує на `docker/crond/prod`, у `php -m` контейнера xdebug відсутній, `docker logs` показує прийнятий розклад `*/5`.

## 4. Документація

- [x] 4.1 Додати коротку примітку в `CLAUDE.md` у розділ «Docker»: cron-сервіс, вибір файла за `APP_ENV`, перебудова образу після зміни. Перевірка: примітка збігається з фактичною поведінкою з 3.2–3.3.
