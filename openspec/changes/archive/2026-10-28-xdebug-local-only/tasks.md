# Tasks

## Межі зміни

- Змінюємо: `docker/php/Dockerfile`, `docker/docker-compose.yml`, один наявний документ про docker (задача 4.1).
- Не чіпаємо: `docker/php/xdebug.ini`, `docker/Makefile`, сервіси nginx/mysql/node, `environment.XDEBUG_MODE`, `.env.example`.
- Нові залежності: немає. Публічні контракти: без змін, з'являється build-arg `APP_ENV`.

## 1. Умовний xdebug у Dockerfile

- [x] 1.1 `docker/php/Dockerfile`: додати `ARG APP_ENV=local` і в наявному `RUN` (до `USER app`) обгорнути `pecl install xdebug && docker-php-ext-enable xdebug` умовою `[ "$APP_ENV" = "local" ]`. Умовність лише в shell `if`; нового `RUN`, stage чи `target` не створювати. Перевірка: `docker build --build-arg APP_ENV=prod -t xd-prod docker/php` і `docker run --rm xd-prod php -m` без `xdebug`; для `APP_ENV=local` з `xdebug`.
- [x] 1.2 Там же: `COPY xdebug.ini` змінити на копіювання в нескановану теку `/usr/local/etc/php/xdebug.ini`; у гілці `local` того ж `RUN` переносити файл у `conf.d/zz-xdebug.ini`, в `else` видаляти. Перевірка: `docker run --rm xd-prod ls /usr/local/etc/php/conf.d /usr/local/etc/php` не показує ini на prod і показує `zz-xdebug.ini` на local.
- [x] 1.3 `docker/docker-compose.yml`: у `services.php.build.args` додати `APP_ENV: ${APP_ENV:-local}` поруч з `HOST_UID`. Перевірка: `cd docker && docker compose --env-file ../.env config` показує `APP_ENV` у build args.

## 2. Перевірка збірки

- [x] 2.1 Матриця сценаріїв зі специфікації (збирати через `APP_ENV=<v> docker compose --env-file ../.env build php`, перевіряти `docker compose --env-file ../.env run --rm --no-deps php php -m | grep -ic xdebug`): `local` — 1, `prod` — 0, `staging` — 0, `env -u APP_ENV` з `.env` без `APP_ENV` — 1. Для prod додатково: ini відсутній у `conf.d` і в `/usr/local/etc/php`. `.env` не редагувати: shell-змінна перекриває `--env-file`.

## 3. Незалежна перевірка

- [x] 3.1 Звірити зміну з правилами проєкту (`AGENTS.md`, `CLAUDE.md`).
- [x] 3.2 Звірити з кожним із 4 сценаріїв `specs/dev-environment/spec.md`: для кожного вказати файл і рядок реалізації та результат з 2.1.
- [x] 3.3 Звірити з `decisions.md`: build-arg, `local` як дефолт, будь-яке інше значення = prod, `XDEBUG_MODE` без змін.
- [x] 3.4 Шукати помилки реалізації: порядок `RUN` і `USER app`, права на перенос/видалення ini, `&&` та `if` у shell.
- [x] 3.5 Звірити `git diff` з розділом «Межі зміни»: жодних файлів поза переліком.

## 4. Закриття

- [x] 4.1 Зазначити в `CLAUDE.md` (розділ Docker), що після зміни `APP_ENV` потрібен `make build`; нових файлів документації не створено.
