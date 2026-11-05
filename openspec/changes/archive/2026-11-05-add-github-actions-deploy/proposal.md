# Proposal

## Why

Деплой ручний: збірка фронтенду, залежності й копіювання файлів робляться вручну, тому легко забути крок (`npm run build`, `migrate`), а прод розходиться з `main`. Зміни без перевірки тестами можуть потрапити у прод. Рішення: `decisions.md`.

## What Changes

- Додати GitHub Actions workflow `.github/workflows/deploy.yml`: тести → збірка → rsync на сервер → міграції та `optimize` по ssh.
- Тригери: push у `main` та ручний запуск (`workflow_dispatch`); деплой лише після зелених тестів.
- На сервер їде тільки Laravel-частина; docker та локальні службові папки виключені; `.env` і `storage` на сервері не чіпаються.
- Доступ до сервера лише через GitHub Secrets.

## Capabilities

### New Capabilities
- `production-deploy`: автоматичний build/deploy Laravel-застосунку на прод-сервер через GitHub Actions (тригери, склад деплою, міграції БД, секрети).

### Modified Capabilities

## Impact

- Нові файли: `.github/workflows/deploy.yml`.
- Налаштування поза репозиторієм: GitHub Secrets і ssh-доступ користувача `deploy` до `/var/www/larafast` на сервері.
- Код застосунку, `docker/` і наявні специфікації не змінюються.
