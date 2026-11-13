# Tasks

Джерела: [decisions.md](decisions.md), [design.md](design.md), `specs/`.

## Межі зміни

Змінюємо: `resources/views/components/*`, `resources/views/pages/home.blade.php`, `resources/css/app.css`, `resources/js/app.js`, `public/images/`, `routes/chat.php`, `bootstrap/app.php` (лише `is('chat')`), `app/Providers/AppServiceProvider.php` (лише limiter), `config/site.php`, `.env.example`, `app/Http/Controllers/ContactController.php`, `app/Http/Requests/ContactRequest.php`, `app/Mail/ContactMessage.php`, `tests/Feature/`, `CLAUDE.md` (розділ про маршрути).
Не чіпаємо: `app/Faq/*`, `ChatController`, `FaqServiceProvider`, `Setting`, Filament, `resources/views/pages/privacy.blade.php` і текст політики, `resources/js/chat.js` (змінюється лише якщо нова розмітка не вкладається в наявні `data-chat*` — тоді правимо розмітку, не скрипт), `openspec/specs/` (оновить архівація).
Нові залежності: немає (ні composer, ні npm, ні CDN: іконки — інлайн SVG, шрифти — наявний `@fonts`).
Публічні контракти: додається `POST /contact`; `POST /chat` без змін.

## 1. Бекенд форми

Передумова для UI: форма має куди надсилати. Тести йдуть перед кодом (`testing-best-practices`).

- [x] 1.1 У `config/site.php` додати `calendar_url` (env `SITE_CALENDAR_URL`, дефолт `https://calendar.app.google/xVjCjasQxYJgKxyN9`); у `.env.example` додати `SITE_CALENDAR_URL` і SMTP-змінні Gmail (`MAIL_MAILER=smtp`, `smtp.gmail.com`, `587`). Перевірка: `php artisan config:show site`.
- [x] 1.2 Спершу `php artisan make:test --phpunit ContactTest`: успіх (`Mail::fake()`, тема `Mam proces, który wymaga automatyzacji.`, отримувач `site.contact_email`, Reply-To), 422 без/з поганим email і при `message` > 3000, honeypot `company_website` (200 без листа), 429 з JSON, збій пошти → 502 JSON. Перевірка: тест падає.
- [x] 1.3 `ContactRequest` (`make:request`) з правилами зі spec `contact-form` і `ContactController` (`make:controller`, invokable) за зразком `app/Http/Controllers/ChatController.php`: JSON `{type: ok|error}`, збій відправки → 502 з `type: error`. Нових абстракцій і шарів не вводити.
- [x] 1.4 `app/Mail/ContactMessage.php` (`make:mail`): тема, Reply-To відвідувача, тіло з name/email/message; відправка синхронна (`Mail::send`, не `queue`). Не додавати подієвих слухачів.
- [x] 1.5 `routes/chat.php`: додати `POST /contact` поруч із `/chat` з `throttle:contact`, без нового route-файлу й без змін у `then:` у `bootstrap/app.php`. Limiter `contact` — в `AppServiceProvider::boot` (не `FaqServiceProvider`: той належить домену FAQ). У `bootstrap/app.php` `withExceptions`: `$request->is('chat')` → `$request->is('chat', 'contact')`, інакше 429 на `/contact` буде HTML. Перевірка: `php artisan test --filter=ContactTest` зелений.

## 2. Новий дизайн головної

Передумова: стилі в `app.css`. Далі компонент за компонентом: кожен пункт лишає сторінку робочою.

Заборони для розділу: не додавати Phosphor/CDN-скриптів і Google Fonts-тегів (іконки — інлайн SVG за зразком `x-ai-chat`); `data-chat`, `data-chat-log`, `data-chat-form`, `data-chat-input`, `data-chat-chip`, `data-max-*` не змінювати.

- [x] 2.1 Перенести токени, `.btn`, `.shell`, анімації й `prefers-reduced-motion` з `.agents/propose/index.html` у `resources/css/app.css`; фото `photo.jpg` — у `public/images/photo.jpg`. Перевірка: `npm run build` проходить.
- [x] 2.2 Оновити `nav.blade.php` і `hero.blade.php` за макетом; кнопки «Umów 15 minut» — `href="{{ config('site.calendar_url') }}"` з `target="_blank" rel="noopener"`. Перевірка: `/` відповідає 200, у `nav` і `hero` немає `mailto:`.
- [x] 2.3 Оновити `intro`, `services`, `projects`, `process` за макетом; нові секції макета (`vs`, `tagline`, `rules`, `rekomendacje`) — окремі компоненти `x-vs`, `x-tagline`, `x-rules`, `x-recommendations` за зразком наявних. Перевірка: порядок секцій у `home.blade.php` збігається з макетом.
- [x] 2.4 `ai-chat.blade.php`: перенести стиль макета, зберегти привітання, підказки й попередження «Rozmawiasz z asystentem AI. Nie podawaj danych wrażliwych.» з посиланням на політику (spec `chat-widget`). Перевірка: `php artisan test tests/Feature/Faq/ChatTest.php` зелений, ручна відправка питання доходить до `/chat`.
- [x] 2.5 `footer.blade.php` (`© {{ now()->year }} Vadym Leontiev`, `mailto:hello@…`, посилання `/polityka-prywatnosci`, без «Regulamin») і `backdrop.blade.php`; `privacy.blade.php` не чіпати. Перевірка: `tests/Feature/PrivacyPolicyTest.php` зелений.
- [x] 2.6 Оновити `tests/Feature/LandingPageTest.php` під новий дизайн: замінити перевірки старих вимог (`href="#chat"` у CTA, `Strony, które sprzedają.`, `data-card=…`, порядок секцій) на нові; зберегти перевірку footer і попередження про AI. Перевірка: `php artisan test tests/Feature/LandingPageTest.php` зелений.

## 3. Форма, дзвінок, копіювання

- [x] 3.1 Спершу в `LandingPageTest` додати тест: `/` містить `href` з `config('site.calendar_url')` і не містить `href="mailto:` у кнопці «Umów 15 minut rozmowy». Перевірка: падає, потім зеленіє після 3.2.
- [x] 3.2 Створити `resources/views/components/contact-form.blade.php` (форма, кнопка «Wyślij», honeypot `company_website`, посилання на політику, блок успіху) і секцію контакту з заголовком «Masz proces, który zjada czas Twojego zespołu?» у `cta.blade.php`. Кнопка «Umów 15 minut rozmowy» і копіювання — там само. Перевірка: тест з 3.1 зелений.
- [x] 3.3 У `resources/js/app.js` додати обробник форми: `fetch('/contact', JSON)`, стани помилок/успіху, лічильник символів, польські тексти для 422/429/5xx за зразком `errorMessages` у `chat.js`. Окремий клас-клієнт не створювати. Перевірка: ручна відправка в браузері; лист з'являється в логах (`MAIL_MAILER=log`).
- [x] 3.4 У `app.js` додати копіювання email: `navigator.clipboard.writeText` з fallback на тимчасовий textarea + `execCommand('copy')`; підпис «skopiowano»/підказка про ручне виділення. Перевірка: клік кладе `hello@vadymleontiev.dev` в буфер на `http://` (fallback) і `https://`.
- [x] 3.5 Перевірити, що привʼязка не зламала чат: в одному завантаженні `app.js` обидва модулі (`chat.js`, форма) ініціалізуються без помилок у консолі. Нових залежностей не додавати.

## 4. Незалежна перевірка

Виконує окремий агент (`/dev-flow:review`) у власному процесі, не той, що писав код. Вхід — діф, специфікації зміни й правила проєкту; контекст реалізації не передається.

- [x] 4.1 Рев'ю коду за правилами проєкту: `.ai/rules/app.md`, `.ai/rules/views.md`, `.ai/rules/faq.md`, `CLAUDE.md`. Перевірити конвенції контролерів, відсутність `strict_types`, `Setting::resolve` для налаштувань чату.
- [x] 4.2 Звірка зі специфікаціями `specs/contact-form` і `specs/landing-page`: для кожної вимоги вказати `файл:функція`, що її виконує; вимога без координати — незакрита.
- [x] 4.3 Пошук помилок реалізації: порожнє/гранично довге повідомлення, збій SMTP посеред запиту, подвійний сабміт, honeypot, 429 як JSON.
- [x] 4.4 Пошук хибної логіки: форма показує «Zapytanie wysłane» при 5xx, копіювання без HTTPS, кнопка календаря відкривається в тій самій вкладці.
- [x] 4.5 Звірка діфа з блоком «Межі зміни»: кожен файл поза межами — дефект.
- [x] 4.6 Знайдене завести виправленнями окремими пунктами або окремою зміною.

## 5. Закриття

- [x] 5.1 Оновити `CLAUDE.md`: `POST /contact` (stateless, throttle `contact`, JSON-429) поруч з описом `/chat`.
- [x] 5.2 `vendor/bin/pint --dirty --format agent`, `php artisan test`, `npm run build` — усе зелене.
- [x] 5.3 Після рев'ю — `/opsx:archive update-landing-design`.

## Виправдана складність

| Що додаємо | Навіщо | Чому простіше не вийде |
|---|---|---|
| `ContactController`, `ContactRequest`, `ContactMessage` | `POST /contact` зі spec `contact-form` | Це мінімум для валідації й листа за зразком `ChatController`; одним класом валідація, відправка й тема змішуються |
| `x-contact-form`, `x-vs`, `x-tagline`, `x-rules`, `x-recommendations` | Нові секції макета | Наявна конвенція «секція = компонент»; один великий компонент ламав би її |
