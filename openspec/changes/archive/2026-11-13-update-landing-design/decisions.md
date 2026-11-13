# Журнал рішень: оновлення дизайну лендінгу

## Проблема
Головна сторінка має старий дизайн. Новий макет (`.agents/propose/index.html`, фото `photo.jpg`) статичний: форма «Masz proces…» нікуди не надсилається, чат не підключений до бекенду, кнопка «Umów 15 minut rozmowy» веде на `mailto:`, а «kliknij, aby skopiować» не копіює адресу. Доки це не виправлено, новий дизайн не може замінити поточний.

## Дано
- Бекенд чату не змінюється: `POST /chat`, `ChatController`, `app/Faq/*` — перевірено: `routes/chat.php`, `app/Http/Controllers/ChatController.php`.
- Контракт чату: запит `messages[]` з `question`/`answer`, відповідь `{type: answer|error, message}`; фронт читає `data-max-messages`, `data-max-chars` — перевірено: `resources/js/chat.js`, `resources/views/components/ai-chat.blade.php`.
- Фронт — Blade-компоненти (`hero`, `nav`, `cta`, `footer` тощо) на Tailwind 4 + Vite; макет — один HTML з inline CSS/JS — перевірено: `resources/views/pages/home.blade.php`, `.agents/propose/index.html`.
- Налаштування чату у views читаються лише через `Setting::resolve` — перевірено: `.ai/rules/views.md`.
- Пошти в застосунку немає (`MAIL_MAILER=log`, немає `app/Mail`, немає `/contact`); `config/site.php` має `contact_email` — перевірено: `.env.example`, `config/site.php`, `routes/`.
- Google-пакетів у проєкті немає — перевірено: `composer show --direct`.

## Концепт рішення
**Обрано:** кнопка «Umów 15 minut rozmowy» веде на публічну сторінку запису Google Calendar (Appointment schedule з автоматичним Meet); бекенду для календаря немає.
**Чий:** згенеровано у воронці (розробник описав мету, концепт не називав)
**Чому:** подію з Meet у календарі власника створює сам Google; це нуль коду й залежностей.
**Відкинуто:**
- Власний слот-пікер + Google Calendar API — OAuth, новий пакет, розрахунок вільних слотів; це окрема фіча.
**ADR:** ні — рішення легко змінюється заміною посилання на кнопці.

## Доменна модель
Не застосовно (коротка зміна фронтенду з одним новим ендпоїнтом; повна воронка обрана через обсяг, не через домен).

## Вектор
```
Blade-компоненти (новий дизайн) ──► app.css / app.js (Vite)
  чат-форма ──fetch──► POST /chat ──► ChatPipeline          (без змін)
  лід-форма ──fetch──► POST /contact ──► ContactController ──► Mail ──► hello@vadymleontiev.dev
  «Umów 15 minut» ──► <a href="{Google Appointment URL}">   (без бекенду)
  «kliknij, aby skopiować» ──► clipboard API + fallback     (чистий JS)
```
- Макет розбивається на Blade-компоненти за поточним ладом, стилі йдуть у Tailwind/Vite, а не inline.
- `chat.js` і data-атрибути `MAX_MESSAGES`/`MAX_CHARS` зберігаються, нова розмітка підключається до них.
- `/contact` — окремий stateless POST поза групою `web` (без сесії і CSRF), як `/chat`, зі своїм throttle.
- URL Google-запису та адреса отримувача живуть у `config/site.php` через env.

## Деталі
### Надсилання листа з форми
**Рішення:** синхронно в межах запиту; при збої SMTP користувач бачить помилку, текст у формі лишається.
**Чому:** в макеті вже є стан помилки форми, а втрачений лід гірший за секунду очікування.
**Альтернатива:** черга — збій невидимий для відвідувача і потрібен worker на проді.
**ADR:** ні — легко змінюється на `queue()`.

## Відкриті питання
- Чи `hello@vadymleontiev.dev` — акаунт Google Workspace: від цього залежить, чи From збігається з автентифікованим акаунтом Gmail SMTP. Налаштування середовища, не код.

## Припущення
- URL запису Google Calendar: `https://calendar.app.google/xVjCjasQxYJgKxyN9` (дефолт `site.calendar_url`, env-перевизначення) — надано власником.
- Пошта: Gmail SMTP (`smtp.gmail.com:587`, app password, 2FA), обсяг 1–2 листи на тиждень, ліміти Gmail неактуальні; From = автентифікований акаунт, Reply-To = email відвідувача — non-blocking.
- Лист: тема `Mam proces, który wymaga automatyzacji.`, отримувач `hello@vadymleontiev.dev` (через `site.contact_email`), `Reply-To` — email відвідувача, тіло — імʼя, email, повідомлення; кнопка форми називається `Wyślij` — non-blocking.
- Захист форми: honeypot з макета + throttle як у `/chat`; валідація email і довжин на сервері — non-blocking.
- Копіювання email: `navigator.clipboard` з fallback на `execCommand('copy')` для небезпечного контексту; хендлер підключається в `app.js` — non-blocking.
- `photo.jpg` переноситься в `public/` і підключається звідти — non-blocking.
- Сторінка `/polityka-prywatnosci` успадковує нові токени/стилі; окремого редизайну тексту немає — non-blocking.
- Копія email у футері й секції контакту береться з `site.contact_email` — non-blocking.
