# Proposal

## Why

Польськомовні відвідувачі сайту не мають способу швидко отримати відповідь про послуги й ціни: FAQ лежить лише в `data/faq.json`, а вільний LLM-чат вигадував би зайве. Без змін питання йдуть у ручну переписку. Рішення зафіксовані в `decisions.md` цієї зміни.

## What Changes

- Ендпоінт `POST /chat` у Laravel-застосунку (контролер + сервісні класи `app/Faq/`), без окремого сервісу й без Python у Docker.
- `/chat` приймає до `MAX_MESSAGES` повідомлень (історія з відповідями + останнє питання без відповіді), переформулює питання з урахуванням історії, шукає в Pinecone, відповідає через Claude (пакет `anthropic-ai/sdk`) лише за знайденим контекстом, формат `{"type":"answer","message":"..."}`.
- Якщо нічого не знайдено вище `VECTOR_MIN_SCORE` — фіксована польська фраза без виклику Claude.
- `/chat` обслуговує php-fpm за тим самим origin (без CORS); частоту запитів по IP обмежує `throttle` Laravel. Конфіг nginx не змінюється.
- Artisan-команда `faq:seed` наповнює Pinecone з `data/faq.json` (ідемпотентна, за зразком JS-сидера, без `lang` і `project`). Pinecone викликається звичайними HTTP-запитами (`Http::`), без SDK.
- Artisan-команда `faq:measure-score` вимірює `VECTOR_MIN_SCORE` (за зразком `measure-score.js`, без rerank).
- Параметри Claude читаються через наявний `Setting::resolve()` (адмін-сторінка Setup перекриває `.env`); `PINECONE_*` додаються в `config/services.php` і `.env.example`.

## Capabilities

### New Capabilities
- `faq-chat`: поведінка `POST /chat`: валідація запиту, переформулювання, пошук у FAQ, відповідь польською лише з контексту, фіксована відповідь «не знайдено», помилки, ліміт частоти на вході.
- `faq-index`: наповнення Pinecone з `data/faq.json` командою `faq:seed` (чанки, ідемпотентність за хешем вмісту) і вимірювання порога `VECTOR_MIN_SCORE` командою `faq:measure-score`.

### Modified Capabilities

(немає)

## Impact

- Нове: `app/Faq/`, `app/Http/Controllers/ChatController.php`, `app/Providers/FaqServiceProvider.php`, `app/Console/Commands/{FaqSeed,FaqMeasureScore}.php`, `routes/chat.php`, тести в `tests/{Unit,Feature}/Faq/`.
- Змінюється: `composer.json`/`composer.lock` (`anthropic-ai/sdk`), `bootstrap/app.php` (підключення `routes/chat.php`, `TrustCloudflare`), `routes/console.php` (розклад оновлення IP Cloudflare), `config/cloudflare.php`, `bootstrap/providers.php`, `config/services.php`, `.env.example`.
- `docker/php/Dockerfile`: додано розширення `intl`, `zip`.
- Не чіпаємо: `docker/*` крім цього (compose, nginx), наявні маршрути, `Setting`, сторінку Setup.
- Зовнішні залежності: Anthropic API через `anthropic-ai/sdk`, Pinecone REST API (інтегроване ембедінг-поле); ключі `ANTHROPIC_API_KEY`, `PINECONE_API_KEY`.
- Вартість: до двох викликів Claude на запит.
- Історія: початково зміна проєктувалась як окремий Python-сервіс (FastAPI + LangChain) і була реалізована так; потім переписана на PHP, Python-код і `ai-service` у compose видалені.
