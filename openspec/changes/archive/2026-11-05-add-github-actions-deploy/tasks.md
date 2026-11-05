# Tasks

## 1. Workflow тестів і збірки

- [x] 1.1 Створити `.github/workflows/deploy.yml` з тригерами `push: main` і `workflow_dispatch`, `concurrency` та job `test` (setup-php 8.3, setup-node, `composer install`, `cp .env.example .env`, `php artisan key:generate`, `php artisan test`); перевірити `php artisan test` локально й валідність YAML (`actionlint` або запуск на гілці)
- [x] 1.2 Додати кроки збірки job `deploy` (`needs: test`): `composer install --no-dev --optimize-autoloader`, `npm ci`, `npm run build`; перевірити, що в робочій копії є `vendor` без dev-пакетів і `public/build/manifest.json`

## 2. Доставка на сервер

- [x] 2.1 Додати крок налаштування ssh з секретів `SSH_KEY`, `SSH_KNOWN_HOSTS` (запис у `~/.ssh/known_hosts`, без `StrictHostKeyChecking=no`; порт `SSH_PORT`); перевірити, що в репозиторії немає ключів чи адрес
- [x] 2.2 Додати крок `rsync -avz --delete` з exclude-списком із `design.md` на `$SSH_USER@$SSH_HOST:$REMOTE_PATH/` (порт `$SSH_PORT`); перевірити через `--dry-run`, що `docker/`, `tests/`, `openspec/`, `.env`, `storage`, `public/storage` не потрапляють у синхронізацію і не видаляються
- [x] 2.3 Додати ssh-крок `cd $REMOTE_PATH && php artisan migrate --force && php artisan optimize`; перевірити, що при помилці міграції job червоний і `optimize` не виконується

## 3. Перевірка

- [x] 3.1 Перевірено: усі секрети (`SSH_KEY`, `SSH_HOST`, `SSH_USER`, `SSH_PORT`, `REMOTE_PATH`, `SSH_KNOWN_HOSTS`) додані в GitHub, публічний ключ додано на сервері, локальний `ssh` по ключу працює
- [x] 3.2 Один раз підготувати сервер вручну: `cp .env.example .env`, `php artisan key:generate`, `chmod 600 .env`, `APP_ENV=production`, `APP_DEBUG=false`, налаштувати `DB_*`; перевірити `ls -l .env` (права 600) і `php artisan about` без помилок
- [x] 3.3 Запустити workflow вручну та переконатися, що job зелений, сайт працює, а міграції застосовано
