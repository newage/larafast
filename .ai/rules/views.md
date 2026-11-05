---
paths:
  - 'resources/views/**'
---

# Views

## Reading Claude/chat tunables
Read Claude and chat tunables (model, effort, max tokens, message limits, min score, API key) with `Setting::resolve($key)` at resolve time. Never use `config('claude.*')` or `env()` directly. Add a new tunable to `config/claude.php` as its default, and expose it on the Setup page.
