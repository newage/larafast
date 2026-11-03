# Design

## Context

Laravel-застосунок у Docker-стеку `php`, `nginx`, `mysql`, `node`. Рішення й історія переходу з Python на PHP — у `decisions.md`; мотивація — у `proposal.md`. Чат stateless: історію присилає клієнт, Pinecone у рантаймі лише читається. Параметри Claude вже керуються через `Setting::resolve()` (`.env` + адмін-сторінка Setup).

## Goals / Non-Goals

**Goals:**
- `POST /chat` у Laravel без нових контейнерів і без Python.
- Один чистий пайплайн: валідація → переформулювання → пошук → відповідь/фіксована фраза.
- Сидер і вимірювач порога як artisan-команди.

**Non-Goals:**
- Автентифікація, сесії, збереження діалогів, веб-інтерфейс чату, стрімінг.
- Rerank, видалення застарілих чанків з індексу.
- Багатомовність: відповіді лише польською.

## Decisions

- **Структура `app/Faq/`:** `ChatRequestValidator`, `ChatMessage`, `ChatPipeline`, `ClaudeChat` (промпти, переформулювання, відповідь), `ClaudeGateway` + `AnthropicGateway`, `PineconeIndex`, `FaqRetriever`, `FaqRecords` (чанки, хеш, diff), `ScoreStats`; винятки `InvalidChatRequest` (422) і `UpstreamFailure` (502). Чиста логіка відокремлена від I/O.
- **Маршрут:** `routes/chat.php`, підключений через `then:` у `bootstrap/app.php`, поза групою `web` (без сесії й CSRF), middleware `throttle:chat`.
- **Rate limit:** `RateLimiter::for('chat')` у `FaqServiceProvider`, 10 запитів/хв по IP. Знімає `limit_req` з nginx; `throttle` стоїть до валідації, тож 429 не тратить токени.
- **Claude:** `anthropic-ai/sdk`, клієнт створюється в `FaqServiceProvider` з `Setting::resolve()`. `ClaudeGateway` (один метод `complete`) існує лише як шов для тестів без мережі, не як провайдер-агностичний шар. Переформулювання: `thinking: disabled`, effort `low`, `max_tokens` 128 (thinking-токени інакше з'їли б ліміт); збій деградує до сирого питання. Відповідь: `CLAUDE_MAX_TOKENS`, `CLAUDE_EFFORT` передається, лише якщо непорожній.
- **Pinecone:** REST через `Http::`: хост індексу береться з `PINECONE_INDEX_HOST` або з `GET api.pinecone.io/indexes/{name}` (кеш на добу); `records/namespaces/{ns}/search` (`top_k` 10, групування за `parent_id`, до 3 записів), `vectors/fetch`, `records/namespaces/{ns}/upsert` (NDJSON, пакети по 96). Версія API `2025-10`.
- **Контракт помилок:** `ChatController` ловить `InvalidChatRequest` → 422 і `UpstreamFailure` → 502, тіло `{"type":"error","message":...}`. Валідація ручна, а не `FormRequest`, щоб формат помилки був єдиним для всіх випадків, включно з не-масивом у `messages`.
- **Команди:** `faq:seed {file?} {--dry-run}`, `faq:measure-score {file?}`; файл за замовчуванням `data/faq.json`.
- **Тести:** PHPUnit; Pinecone — `Http::fake`, Claude — підміна `ClaudeGateway` у контейнері.

## Risks / Trade-offs

- Якість переформулювання (модель може спотворити питання) → лише за наявності історії, збій деградує до сирого питання; перевірити на живих діалогах, `faq:measure-score` багатокрокові розмови не покриває.
- До двох викликів Claude на запит → `throttle`, малий `max_tokens` і вимкнений thinking для переформулювання.
- `throttle` по IP зачіпає користувачів за спільним NAT → значення в `FaqServiceProvider`.
- Застосунок за Cloudflare: `TrustCloudflare` (підклас `TrustProxies`) довіряє `X-Forwarded-*` лише від діапазонів Cloudflare; підроблений `X-Forwarded-For` ігнорується. Список лежить у `storage/app/cloudflare-ips.json`, його кожні 10 хвилин оновлює `cloudflare:refresh-ips` (розклад `everyTenMinutes`, `withoutOverlapping`); невалідна чи недоступна відповідь лишає попередній список, вбудований список — запасний. Локально (`APP_ENV=local`) перевірка вимкнена (`config/cloudflare.php`, `TRUST_CLOUDFLARE` перекриває). Розклад працює лише за запущеного `schedule:run` (cron на хості: `* * * * * php artisan schedule:run`); в Docker-стеку планувальника немає. Якщо nginx колись стане за іншим проксі, його адреси треба довіряти окремо.
- `VECTOR_MIN_SCORE=0.84` — стартове значення → виміряти `faq:measure-score` після наповнення індексу й підправити.
- php-образ не мав `ext-intl` (його вимагає `filament/support`) і `ext-zip`: додані в `docker/php/Dockerfile` (`libicu-dev`, `libzip-dev`) і в `require` у `composer.json`. Правило xdebug з `CLAUDE.md` не зачеплено.
- Розбіжність назви індексу (`develop` у `.env.example` проти `faq` у ТЗ) → значення задає `.env`.

## Migration Plan

1. `composer install`, задати ключі в `.env`.
2. `php artisan faq:seed --dry-run`, потім `php artisan faq:seed`.
3. `php artisan faq:measure-score`, за потреби змінити `VECTOR_MIN_SCORE`.
4. Rollback: прибрати `routes/chat.php` з `bootstrap/app.php`; індекс Pinecone можна лишити.
