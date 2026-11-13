# Design

## Context

Поточна головна — Blade-компоненти на Tailwind 4/Vite; чат підключений через `resources/js/chat.js` і data-атрибути. Макет — один HTML з inline CSS/JS. Пошти в застосунку немає (`MAIL_MAILER=log`). Мотивація: див. proposal.md.

## Goals / Non-Goals

**Goals:** замінити дизайн, зберегти контракт чату, додати форму, дзвінок і копіювання.
**Non-Goals:** зміни `app/Faq/*`, `POST /chat` і spec `chat-widget`; Calendar API; редизайн тексту політики приватності; черга для пошти.

## Decisions

- **Блейд-компоненти замість одного файлу.** Макет розбивається за поточним ладом (`x-hero`, `x-nav`, …), стилі переходять у `app.css`, скрипти — в `app.js`/модулі. Альтернатива (покласти HTML цілком) ламає конвенції й views-правила.
- **Дзвінок — звичайне посилання** на Google Appointment Schedule; URL у `config/site.php` (`calendar_url`, env). Альтернатива (Calendar API + слот-пікер) відкинута: OAuth, пакет, логіка слотів.
- **`POST /contact` у `routes/chat.php`** (вже поза групою `web`, без сесії й CSRF), без нового route-файлу й без змін у `then:`. Limiter `contact` — в `AppServiceProvider`, а не `FaqServiceProvider` (той належить домену FAQ). JSON-429 для `/contact` — розширення `is('chat')` у `bootstrap/app.php`. `ContactController` + `ContactRequest` + `ContactMessage` (Mailable); `Mail::to(config('site.contact_email'))->send()` синхронно; збій → 502 JSON `type: error`.
- **Honeypot** `company_website`: непорожнє → 200 без листа.
- **Копіювання:** `navigator.clipboard.writeText`, fallback `execCommand('copy')` через тимчасовий textarea.
- **Чат:** лишається привітання, підказки й текст «Rozmawiasz z asystentem AI…» зі спеки `chat-widget`, а не з макета (текст макета «Nie wpisuj danych osobowych…» не використовується).
- **Іконки й шрифти:** інлайн SVG замість Phosphor з `unpkg`; шрифти через наявний `@fonts` — нових зовнішніх залежностей немає.
- **Фото** переноситься в `public/images/`.

## Risks / Trade-offs

- Синхронна пошта → повільний запит при збої SMTP → таймаут і явна помилка у формі.
- Gmail SMTP підставляє акаунт як From → Reply-To = email відвідувача.
- Replaced-вимоги `landing-page` можуть мати тести → оновити тести разом із UI.

## Migration Plan

Env: `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, логін і app password, `SITE_CONTACT_EMAIL`, `SITE_CALENDAR_URL`. Відкат: повернути попередній Blade/CSS комітом.

## Open Questions

- Чи `hello@vadymleontiev.dev` є акаунтом Workspace (впливає лише на From).
