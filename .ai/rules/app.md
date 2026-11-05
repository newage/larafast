---
paths:
  - 'app/**'
---

# App

## Reading Claude/chat tunables
Read Claude and chat tunables (model, effort, max tokens, message limits, min score, API key) with `Setting::resolve($key)` at resolve time. Never use `config('claude.*')` or `env()` directly. Add a new tunable to `config/claude.php` as its default, and expose it on the Setup page.

## No strict_types declaration
Don't add `declare(strict_types=1)` to PHP files.
