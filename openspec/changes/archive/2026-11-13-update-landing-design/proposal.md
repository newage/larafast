# Proposal

## Why

Новий дизайн головної (`.agents/propose/index.html`) існує лише як статичний макет: форма нікуди не надсилається, кнопка дзвінка веде на `mailto:`, копіювання email не працює. Поки це не підключено, його не можна викотити замість поточного. Джерело рішень: `decisions.md`.

## What Changes

- Головна сторінка повністю переходить на новий дизайн (Blade-компоненти, Tailwind/Vite, фото `photo.jpg`); усе, що зараз ведe на чат-якір `#chat`, перекладається на нову структуру секцій.
- Чат на сторінці лишається з тим самим бекендом `POST /chat` і `chat.js`; змінюється лише розмітка, до якої він підключається.
- Нова форма «Masz proces, który zjada czas Twojego zespołu?» з кнопкою «Wyślij» надсилає лист на `hello@vadymleontiev.dev` з темою `Mam proces, który wymaga automatyzacji.` через новий `POST /сontaсt`.
- Кнопка «Umów 15 minut rozmowy» веде на публічну сторінку запису Google Calendar (Meet додає Google); замінює `mailto:`.
- «kliknij, aby skopiować» справді копіює email у буфер.
- **BREAKING (для специфікації):** вимоги `landing-page` про кнопки на `#chat`, текст секції контакту, hero «Przykładowy proces» і анімації макета застаріли й замінюються.

## Capabilities

### New Capabilities
- `contact-form`: надсилання запиту з форми на пошту власника, валідація, захист від спаму, стани успіху й помилки.

### Modified Capabilities
- `landing-page`: кнопки-заклики (дзвінок у Google Calendar, копіювання email), секція контакту з формою, нова структура й анімації сторінки; футер зберігається.

## Impact

- `resources/views/components/*`, `resources/views/pages/home.blade.php`, `resources/css/app.css`, `resources/js/app.js`, `public/` (фото).
- Новий маршрут `routes/chat.php`-стилю (`POST /contact`), контролер, `Mailable`, `config/site.php`, `.env.example`.
- Без нових Composer-залежностей. `app/Faq/*` і `POST /chat` не змінюються; spec `chat-widget` не змінюється.
