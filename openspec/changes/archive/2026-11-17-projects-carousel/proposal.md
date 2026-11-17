# Proposal

## Why

У розділі «Realizacje» 3 плитки в сітці на 3 колонки. Додається Nordleaf, згодом ще один проєкт: сітка 3+N розсипається, секція росте вниз. Потрібен горизонтальний ряд, який вміщає довільну кількість плиток.

## What Changes

- Плитки проєктів у «Realizacje» стають горизонтальною каруселлю на CSS scroll-snap зі стрілками ‹ › (невеликий JS у `resources/js/app.js`), без нових залежностей.
- Додається плитка Nordleaf (`https://nordleaf.vadymleontiev.dev/`, Astro · TypeScript · Decap CMS) першою; статус «w trakcie» стоїть у полі `meta`.
- Видима кількість плиток зберігає поточні брейкпоінти: 3 / 2 / 1.
- Не змінюється: решта плиток і їхні тексти (зокрема незакомічена правка тегів vadymleontiev.dev).

## Capabilities

### New Capabilities
<!-- немає -->

### Modified Capabilities
- `landing-page`: додається вимога до секції «Realizacje» (карусель, порядок плиток, стрілки).

## Impact

- `resources/views/components/projects.blade.php` (масив `$projects`, розмітка стрілок)
- `resources/css/app.css` (`.projects`, `.proj`, адаптив, стрілки)
- `resources/js/app.js` (логіка стрілок)
- Нових залежностей і бекенд-змін немає; ADR не потрібні.
