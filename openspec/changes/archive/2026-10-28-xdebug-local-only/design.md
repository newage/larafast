# Design

## Context

Dockerfile сервісу php зараз ставить xdebug безумовно. Compose запускається через `docker/Makefile` з `--env-file ../.env`, тож `APP_ENV` з кореневого `.env` доступний у `docker-compose.yml`. Мотивацію див. у proposal.md.

## Goals / Non-Goals

**Goals:**
- Образ без xdebug для будь-якого `APP_ENV`, окрім `local`.

**Non-Goals:**
- Multi-stage збирання, окремі compose-файли чи override.
- Зміна `XDEBUG_MODE` і вмісту `xdebug.ini`.

## Decisions

- **Build-arg `APP_ENV` з default `local`** в Dockerfile та `${APP_ENV:-local}` у compose, щоб збирання без `.env` не ламалось. Відкинуто: multi-stage з `target:` (більша зміна структури); xdebug завжди + `XDEBUG_MODE=off` (суперечить «не встановлювати»).
- **Умовність лише в shell `if` усередині наявного `RUN`** (до `USER app`, бо потрібен root): для `local` ставить і вмикає xdebug та переносить ini в `conf.d/zz-xdebug.ini`, інакше видаляє ini. `COPY` умовним бути не може, тому `xdebug.ini` копіюється завжди в нескановану теку (`/usr/local/etc/php/xdebug.ini`). Нового `RUN`, stage чи `target` не створюємо.
- **Рівно `local` вмикає xdebug**, будь-яке інше значення вважається prod.
- **`XDEBUG_MODE` не чіпаємо**: на prod без xdebug змінна нешкідлива.

## Risks / Trade-offs

- [Змінили `APP_ENV`, але не перезібрали образ] → у документації/задачі вказати `make build`.
- [На prod лишається мертва змінна `XDEBUG_MODE`] → прийнято, нешкідлива.
