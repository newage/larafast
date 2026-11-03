# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Laravel 13 / PHP ^8.3 app with a Filament 5 admin panel. Its main feature is a public FAQ chatbot: Pinecone for retrieval, Claude (via `anthropic-ai/sdk`) for answers. Specs, comments and docs are partly in Ukrainian.

`AGENTS.md` holds only a generic Laravel Boost bootstrap block; Boost is not installed, so ignore it.

## Commands

- Dev environment runs in Docker from `docker/` (`make up|down|build|console-php|console-node`). `make` passes `--env-file ../.env`, so `APP_ENV`, `HOST_UID`, `HOST_GID`, `NGINX_PORT` and `DB_*` come from the root `.env`.
- Tests (PHPUnit 12, SQLite in-memory): `php artisan test`, or `composer test` (clears config first). Single test: `php artisan test --filter=ChatTest` or `php artisan test tests/Feature/Faq/ChatTest.php`.
- `.githooks/pre-commit` runs `php artisan test`, or the same command in the `php` container if PHP isn't on the host. Skip it with `--no-verify`. It is wired up via `core.hooksPath=.githooks` (already set here; set it again on a fresh clone).
- Style: `vendor/bin/pint`.
- Dev server, queue, logs and Vite: `composer dev` (runs `php artisan dev`). Frontend: `npm run build`.
- FAQ index: `php artisan faq:seed [file] [--dry-run]` upserts `data/faq.json` into Pinecone. `php artisan faq:measure-score` is a tuning helper for `VECTOR_MIN_SCORE`. `data/http/chat.http` has sample requests for the chat endpoint.

## Architecture

### FAQ chat (`app/Faq/`, `routes/chat.php`)
- `POST /chat` is a stateless JSON endpoint. It is registered in `bootstrap/app.php` via `then:` outside the `web` group (no session or CSRF). It is throttled by the `chat` limiter (10/min/IP, defined in `FaqServiceProvider`), and 429s on `/chat` are rendered as JSON there.
- `ChatPipeline::run` does the work:
  1. `ChatRequestValidator` validates the message history (throws `InvalidChatRequest`).
  2. `ClaudeChat::rewriteQuestion` turns the last question plus history into a search query.
  3. `FaqRetriever` queries `PineconeIndex`, keeping only hits at or above `VECTOR_MIN_SCORE`.
  4. With no hits it returns `ClaudeChat::NOT_FOUND_MESSAGE` without calling Claude again. Otherwise `ClaudeChat::answer` generates the reply.
  5. Upstream failures are wrapped in `UpstreamFailure`.
- The landing-page widget (`resources/views/components/ai-chat.blade.php`, `resources/js/chat.js`) calls `POST /chat`, keeps the history in page memory and sends the last `MAX_MESSAGES − 1` pairs plus the new question. `MAX_MESSAGES` and `MAX_MESSAGE_CHARS` reach it as `data-max-messages` / `data-max-chars` from `Setting::resolve`. `MAX_MESSAGE_CHARS` limits only `question`, not `answer`. Errors are shown as Polish texts chosen by HTTP status.
- `ClaudeGateway` is the interface to the Anthropic API; `AnthropicGateway` implements it. Tests fake the gateway.
- `FaqServiceProvider` builds the whole graph. Every tunable (model, effort, max tokens, message limits, min score, API key) is read through `Setting::resolve($key)`, **at resolve time, not boot time**.
- `Setting::resolve` returns the DB value (editable in the Filament admin) when it is non-empty, otherwise the `config('claude.*')` default from `.env`. Only `ANTHROPIC_API_KEY` is stored encrypted.
- `faq:seed` upserts in batches of 96 and sends only records whose `content_hash` is new or changed. It never deletes.

### Cloudflare
`TrustCloudflare` replaces Laravel's `TrustProxies`, so the client IP is honoured from `X-Forwarded-For` only when the peer is a Cloudflare edge. The edge ranges are refreshed by the scheduled `cloudflare:refresh-ips`, which only runs when `config('cloudflare.enabled')` is true.

### Scheduling and cron
The schedule lives in `routes/console.php`. The `cron` docker service runs `supercronic docker/crond/${APP_ENV:-local}`. Each environment has its own crontab file, so change cron lines in `docker/crond/local` or `docker/crond/prod`. Prod runs `*/5 * * * * php artisan schedule:run`, so scheduled tasks should not be more frequent than that there.

### Docker
- Xdebug is installed into the php image (`docker/php/Dockerfile`) only when `APP_ENV=local`. Any other value, including `prod`, gives an image without xdebug and without `xdebug.ini`; an unset `APP_ENV` behaves as `local`. After changing `APP_ENV`, rebuild with `make build`.
- Services: `php`, `cron` (same image as `php`), `nginx`, `mysql`, `node`.

## Spec-driven workflow (OpenSpec)

- Behaviour is specified in `openspec/specs/<capability>/spec.md` (`claude-settings`, `cron-scheduler`, `dev-environment`, `faq-chat`, `faq-index`).
- Changes are proposed under `openspec/changes/<name>/` (proposal, design, decisions, tasks, spec deltas) and moved to `openspec/changes/archive/` when done. Slash commands: `/opsx:explore`, `/opsx:propose`, `/opsx:apply`, `/opsx:archive`.
- Check the relevant spec before changing behaviour, and update it with the code.
