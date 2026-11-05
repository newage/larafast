# Design

## Context

Деталі й відкинуті альтернативи: `decisions.md`. Стек: PHP ^8.3, Vite 8, тести на SQLite in-memory (`php artisan test`). `vendor` та `public/build` у `.gitignore`, тому збираються в CI. Прод у docker не деплоїться: на сервері вже є каталог `/var/www/larafast` із `.env` і `storage`.

## Goals / Non-Goals

**Goals:**
- Один відтворюваний шлях від `main` до проду без ручних кроків.

**Non-Goals:**
- Атомарні релізи (`releases/` + symlink) і автоматичний відкат.
- Деплой docker-стеку, перезапуск контейнерів, налаштування сервера.
- Збірка на сервері.

## Decisions

- **Один workflow, два jobs:** `test` → `deploy` (`needs: test`). Тригери `push: main` + `workflow_dispatch`; `concurrency` без скасування запущеного деплою, щоб два деплої не йшли одночасно.
- **Збірка в CI:** `setup-php` 8.3, `setup-node`, кеш composer/npm. У `test` — `composer install` з dev і `.env` з `.env.example`; у `deploy` — `composer install --no-dev --optimize-autoloader`, `npm ci`, `npm run build`.
- **rsync з exclude-списком** (`-avz --delete`): `.git`, `.github`, `.githooks`, `.agents`, `.ai`, `.claude`, `.idea`, `openspec`, `docker`, `node_modules`, `tests`, `data/http`, `.env`, `storage`, `public/storage`, `bootstrap/cache`, а також dev-файли `.mcp.json`, `boost.json`, `CLAUDE.md`, `phpunit.xml`, `.phpunit.result.cache`. Exclude захищає ці шляхи від `--delete` на сервері. Єдиний шлях призначення `$REMOTE_PATH` = `/var/www/larafast`; порт `$SSH_PORT` передається в `rsync -e "ssh -p …"` і в `ssh -p`.
- **ssh-крок:** `cd $REMOTE_PATH && php artisan migrate --force && php artisan optimize`; креди БД беруться з серверного `.env`.
- **Секрети:** `SSH_HOST`, `SSH_USER`, `SSH_PORT`, `REMOTE_PATH`, `SSH_KNOWN_HOSTS` і приватний ключ `SSH_KEY` (усі вже додані). Host key записується з `SSH_KNOWN_HOSTS` у `~/.ssh/known_hosts`, без `StrictHostKeyChecking=no`.
- **Первинне налаштування сервера (поза workflow, один раз):** `cp .env.example .env`, `php artisan key:generate`, `chmod 600 .env`, `APP_ENV=production`, `APP_DEBUG=false`. Workflow `.env` не створює й `key:generate` не запускає: `APP_KEY` шифрує `ANTHROPIC_API_KEY` у БД, його зміна зробить значення нечитним.
- **Альтернатива** (releases + symlink) відкинута: потребує змін nginx і структури сервера.

## Risks / Trade-offs

- Під час rsync у живу папку можливе коротке вікно зі змішаними файлами → sync останнім кроком після збірки, без проміжних кроків на сервері.
- Міграція падає після rsync: нові файли вже на сервері → відкат вручну; міграції писати зворотно-сумісними.
- `public/storage` та `bootstrap/cache` на сервері створені поза репозиторієм → винесені в exclude, `optimize` перегенерує кеш.
- Перший деплой потребує ручної підготовки сервера (`.env` як вище) і ssh-ключа в секреті `SSH_KEY`.

## Open Questions

- Чи потрібен перезапуск php-fpm/opcache і `queue:restart`: у docker-стеку немає окремого queue-сервісу, а opcache за замовчуванням перевіряє timestamps; за потреби додається окремим кроком без зміни специфікації.
