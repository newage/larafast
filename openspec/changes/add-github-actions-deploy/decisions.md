# Журнал рішень: build/deploy через GitHub Actions

## Проблема
Деплой ручний: фронтенд і залежності збираються вручну, файли копіюються на сервер. Легко забути крок (`npm run build`, `migrate`), прод може розійтися з `main`, а у `main` можуть потрапити зміни, що не пройшли тести.

## Дано
- `.github/` відсутній, workflow немає — перевірено: `ls -a`.
- Вихідний концепт розробника — `.agents/propose/deploy.md`: composer --no-dev, npm ci, npm run build, rsync, ssh migrate + optimize.
- Тести: `php artisan test` (PHPUnit 12, SQLite in-memory); PHP ^8.3; Vite 8 — перевірено: `CLAUDE.md`, `composer.json`, `package.json`.
- `vendor` і `public/build` у `.gitignore`, тому збираються в CI і їдуть через rsync — перевірено: `.gitignore`.
- Деплоїться тільки Laravel-частина; `docker/`, `.git*`, `.githooks`, `.agents`, `.ai`, `.claude`, `.idea`, `openspec`, `node_modules`, `tests`, `data/http` не деплоїмо.
- `.env` і `storage` на сервері не чіпаємо.
- шлях проєкту `/var/www/larafast` — він і є єдиним шляхом призначення для rsync та ssh.

## Концепт рішення
**Обрано:** один workflow `.github/workflows/deploy.yml`: job тестів, потім job деплою (збірка в CI, rsync на сервер, ssh `migrate --force` + `optimize`).
**Чий:** запропонував розробник
**Чому:** простий, без змін структури сервера, збірка відбувається на runner, а не на проді.
**Відкинуто:**
- Збірка на сервері (git pull + composer/npm там) — на проді потрібні node/composer-dev, довше і ризикованіше; розробник свідомо обрав збірку в CI.
**ADR:** ні — легко змінюється, не перетинає межу контексту.

## Вектор
```
push main / workflow_dispatch
  → job test:   composer install, npm ci, php artisan test
  → job deploy: composer install --no-dev -o, npm ci, npm run build
                rsync (лише Laravel-файли, vendor + public/build)
                ssh: migrate --force, optimize
```
- Деплой залежить від успіху тестів (`needs: test`).
- Секрети SSH (`SSH_HOST`, `SSH_USER`, `SSH_PORT`, `REMOTE_PATH`, `SSH_KNOWN_HOSTS`, приватний ключ) лежать у GitHub Secrets, не в репозиторії.
- Один шлях призначення на сервері для rsync і ssh.
- Докер і локальні папки виключено з rsync.

## Деталі
### Тригер і захист
**Рішення:** push у `main` плюс `workflow_dispatch`; деплой лише після зелених тестів.
**Чому:** авто-деплой без ручних кроків, а ручний запуск дозволяє перекатити без нового коміту.
**Альтернатива:** лише push (немає ручного перезапуску); лише ручний (можна забути задеплоїти).
**ADR:** ні — легко змінити в YAML.

### Заміна файлів на сервері
**Рішення:** `rsync --delete` у живу папку, без releases/symlink.
**Чому:** сайт малий, структура сервера не змінюється.
**Альтернатива:** `releases/<sha>` + symlink `current` — атомарно й з відкатом, але потребує змін nginx і структури сервера.
**ADR:** ні — міграція на releases можлива пізніше.

### Міграції БД на проді
**Рішення:** після rsync деплой-job виконує по ssh `php artisan migrate --force` у `/var/www/larafast`, і лише потім `php artisan optimize`. Міграції йдуть на проді з серверного `.env` (креди БД у CI не потрібні).
**Чому:** вимога розробника; `--force` потрібен, бо на проді `APP_ENV=production` і без нього artisan питає підтвердження.
**Наслідок:** якщо міграція падає, job стає червоним, але нові файли вже на сервері (наслідок rsync у живу папку); відкат — вручну.
**Альтернатива:** міграції окремим ручним кроком — відкинуто, бо це повертає забуті кроки з «Проблеми».
**ADR:** ні — один рядок у workflow.

## Відкриті питання
- Чи треба перезапускати php-fpm/opcache і чергу (`queue:restart`) після деплою — залежить від налаштувань прод-сервера; вирішити під час `propose`/`apply`.

## Припущення
- Секрети GitHub (вже додані розробником): `SSH_HOST`, `SSH_USER`, `SSH_PORT`, `REMOTE_PATH` (шлях проєкту на сервері, `/var/www/larafast`), `SSH_KNOWN_HOSTS` (вивід `ssh-keyscan -p 22`) і `SSH_KEY` (приватний ssh-ключ).
- Первинне налаштування сервера робиться вручну один раз і не входить у workflow: `cp .env.example .env`, `php artisan key:generate`, `chmod 600 .env`, `APP_ENV=production`, `APP_DEBUG=false`. `key:generate` у workflow НЕ запускається: зміна `APP_KEY` зламала б сесії та зашифрований `ANTHROPIC_API_KEY` у `Setting`.
- `rsync` виключає `.env` і `storage`, тому їх не перезаписує і не видаляє `--delete`-ом; `bootstrap/cache` виключаємо або перегенеровуємо через `optimize`.
- Node 20+ і PHP 8.3 на runner через `setup-php` / `setup-node`.
- Host key береться з секрету `SSH_KNOWN_HOSTS` і пишеться в `~/.ssh/known_hosts` (без `StrictHostKeyChecking=no` і без `ssh-keyscan` під час запуску).
