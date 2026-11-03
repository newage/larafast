# Design

## Context

Compose-стек (`docker/docker-compose.yml`): php (fpm), nginx, mysql, node. Образ php збирається з `docker/php/Dockerfile` (`php:8.5-fpm`, користувач `app`); `APP_ENV` береться з кореневого `.env` через `make` (`--env-file ../.env`). Код проєкту монтується томом `..:/var/www/larafast`. Мотивація — див. proposal.md; джерело рішень — `decisions.md`.

## Goals / Non-Goals

**Goals:**
- Окремий контейнер, що запускає `schedule:run` за розкладом, залежним від `APP_ENV`.
- Розклад — у нативних crontab-файлах під контролем версій.

**Non-Goals:**
- Зміни коду застосунку чи `routes/console.php`.
- Черги, supervisor, інші фонові процеси.
- JSON або інший проміжний формат розкладу.

## Decisions

- **Сервіс `cron` з того ж образу php.** У compose: `build` ідентичний `php`, `command: supercronic /var/www/larafast/docker/crond/${APP_ENV:-local}`, той самий том, `depends_on: mysql`. Один образ — одна збірка. Альтернатива: окремий Dockerfile — відкинуто, дублює базу.
- **Нативний crontab.** Файли `docker/crond/prod` і `docker/crond/local`; коментарі замінюють описові поля. Альтернатива: JSON + конвертер — зайвий скрипт.
- **supercronic.** Читає crontab як є, передає змінні контейнера, працює від `app`, лог у stdout. Додається в `docker/php/Dockerfile` (завантаження бінарника з фіксованою версією, перевірка sha256). Альтернатива: Debian `cron` — root, змінні вручну, лог у файл.
- **Вибір файла через `${APP_ENV:-local}` у `command`.** Узгоджено з `APP_ENV` для збірки (див. `dev-environment`); без логіки в скриптах.
- **Вміст:** prod — `*/5 * * * * php artisan schedule:run`; local — `* * * * * php artisan schedule:run` (припущення).
- **Змінні оточення.** php-контейнер отримує змінні застосунку з `.env` самого Laravel (читає його з тому), тож cron-команда також бере їх з `.env`; додаткове прокидання не потрібне.
- **Робоча директорія.** Образ має `WORKDIR /var/www/larafast`, supercronic успадковує її.

## Risks / Trade-offs

- [Бінарник supercronic потрапляє і в php-fpm образ] → незначний розмір; прийнятно заради одного образу.
- [Завантаження supercronic під час збірки залежить від мережі/GitHub] → фіксована версія та перевірка sha256.
- [Збій команди тихо губиться, якщо не дивитися логи] → лог у stdout, видно через `docker logs`.
- [Крок `*/5` на проді означає затримку до 5 хв для хвилинних завдань Laravel] → свідоме рішення розробника; `cloudflare:refresh-ips` раз на 10 хв не страждає.
- [Різні `APP_ENV` при збірці й запуску дадуть образ без xdebug з local-розкладом або навпаки] → обидва значення читаються з одного `.env`.

## Open Questions

- Конкретна версія supercronic і її sha256 — фіксуються при реалізації, на специфікацію і підхід не впливають.
