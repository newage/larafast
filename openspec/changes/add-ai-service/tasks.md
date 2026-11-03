# Tasks

## Межі зміни

Змінюємо: `docker/php/Dockerfile`, `app/Faq/` (нове), `app/Http/Controllers/ChatController.php`, `app/Providers/FaqServiceProvider.php`, `app/Console/Commands/{FaqSeed,FaqMeasureScore}.php`, `routes/chat.php`, `bootstrap/app.php` (`then:` і заміна `TrustProxies`), `app/Http/CloudflareProxies.php`, `app/Http/Middleware/TrustCloudflare.php`, `app/Console/Commands/CloudflareRefreshIps.php`, `config/cloudflare.php`, `routes/console.php`, `bootstrap/providers.php`, `config/services.php`, `composer.json`/`composer.lock`, `.env.example` (лише доповнювати), `tests/{Unit,Feature}/Faq/`.
Не чіпаємо: `docker/*`, крім `docker/php/Dockerfile` (лише розширення `intl`, `zip`; блок xdebug не чіпати), `app/Models/Setting.php`, `config/claude.php`, сторінку Setup, `routes/web.php`, `data/faq.json`, `.agents/propose/*` (JS-зразки, лише читати).
Нові залежності: `anthropic-ai/sdk` (єдина). Pinecone — через `Http::`, без SDK.
Публічні контракти: новий `POST /chat`; наявні шляхи без змін.

Порядок: спершу тест за сценаріями specs (`faq-chat`, `faq-index`), потім код.

## 1. Python → PHP: прибрати старе

- [x] 1.1 Видалити `ai-service/` (Python-код, тести, Dockerfile).
- [x] 1.2 Відкотити `docker/docker-compose.yml` (сервіс `ai-service`, `depends_on`) і `docker/nginx/default.conf` (`limit_req_zone`, `location /chat`) до закоміченого стану; перезапустити nginx; перевірити `nginx -t`.

## 2. Каркас

- [x] 2.1 Додати `intl` і `zip` у `docker/php/Dockerfile`, `make build`; `composer require anthropic-ai/sdk ext-intl ext-zip`; перевірити `php -m` і `composer check-platform-reqs`.
- [x] 2.3 Довіряти проксі Cloudflare: `CloudflareProxies`, `TrustCloudflare` замість `TrustProxies` у `bootstrap/app.php`, вимкнено при `APP_ENV=local`; тест `TrustedProxiesTest` (IP з `X-Forwarded-For` лише від Cloudflare, підробка ігнорується, локально вимкнено).
- [x] 2.4 `cloudflare:refresh-ips` (валідація CIDR, атомарний запис, збій лишає старий список) і розклад кожні 10 хвилин у `routes/console.php`.
- [ ] 2.5 На проді: додати cron `* * * * * php artisan schedule:run` і перевірити `php artisan cloudflare:refresh-ips`.
- [x] 2.2 `config/services.php` — блок `pinecone` (`api_key`, `index_name`, `host`, `text_field`, `namespace`); `.env.example` — додати `PINECONE_INDEX_HOST`.

## 3. Індекс: `faq:seed` і `faq:measure-score`

- [x] 3.1 `tests/Unit/Faq/FaqRecordsTest.php` за сценаріями `faq-index`, потім `app/Faq/FaqRecords.php` (`content_hash` від `[embedded, answer, type]`, тим самим форматом, що й у вже наповненому індексі).
- [x] 3.2 `app/Faq/PineconeIndex.php` (search, fetch, upsert NDJSON, пошук хоста) і `app/Console/Commands/FaqSeed.php`: `--dry-run`, пакети по 96, нічого не видаляти; тест `FaqCommandsTest` (dry-run без ключів → 130 чанків, без ключів → помилка з назвою змінної).
- [x] 3.3 `tests/Unit/Faq/ScoreStatsTest.php`, потім `app/Faq/ScoreStats.php` і `app/Console/Commands/FaqMeasureScore.php` без rerank-гілки.
- [ ] 3.4 З живими ключами: `php artisan faq:seed --dry-run`, `faq:seed`, повторний `faq:seed` (очікувано 0 нових і 0 змінених), `faq:measure-score`.

## 4. Пайплайн /chat

- [x] 4.1 `tests/Unit/Faq/ChatRequestValidatorTest.php`, потім `ChatRequestValidator`, `ChatMessage`, `InvalidChatRequest`.
- [x] 4.2 `FaqRetriever` (пошук, групування за `parent_id`, поріг, до 3 записів), `ClaudeChat`, `ClaudeGateway`/`AnthropicGateway`, `ChatPipeline`, `UpstreamFailure`; сценарії в `tests/Feature/Faq/ChatTest.php` з `Http::fake` і підміною `ClaudeGateway`.
- [x] 4.3 `ChatController`, `routes/chat.php`, `then:` у `bootstrap/app.php`, `FaqServiceProvider` (зв'язки, `RateLimiter::for('chat')`) у `bootstrap/providers.php`; тести 200/422/502/429.
- [x] 4.4 Перевірити через nginx: `POST /chat` з невалідним тілом → 422 у форматі помилки, `/` → 200.

## 5. Наскрізна перевірка з живими ключами

- [ ] 5.1 З наповненим індексом і виміряним `VECTOR_MIN_SCORE`: питання з FAQ, off-topic, follow-up «A ile to kosztuje?» після питання про chatbota; перевірити, що `AnthropicGateway` приймає `thinking: disabled` + `outputConfig.effort` для `claude-haiku-5-5` (тести цей виклик підміняють); результат записати сюди.

## 6. Незалежна перевірка

Виконує окремий агент (`dev-flow:review`) з чистим контекстом: діф, специфікації зміни, правила проєкту.

- [ ] 6.1 Рев'ю за правилами проєкту (`CLAUDE.md`, `openspec/specs/dev-environment/spec.md`, `openspec/config.yaml`): ключі лише з конфігу, обробка помилок.
- [ ] 6.2 Звірка зі `specs/faq-chat` і `specs/faq-index`: для кожної вимоги назвати місце в коді (`<файл>:<метод>`).
- [ ] 6.3 Граничні значення (рівно `MAX_MESSAGES`, `MAX_MESSAGE_CHARS`, один елемент), порядок операцій, обрив seed посередині.
- [ ] 6.4 Звірка діфа з «Межі зміни».

## 7. Закриття

- [ ] 7.1 Повний `php artisan test` у контейнері і `openspec validate add-ai-service --strict`.
