# Tasks

Джерела: [decisions.md](decisions.md), [design.md](design.md), `specs/`.

**Межі зміни.**
- Чіпаємо лише: `app/Faq/ChatRequestValidator.php`, `tests/Unit/Faq/ChatRequestValidatorTest.php`, `resources/views/components/ai-chat.blade.php`, `resources/js/chat.js`, `tests/Feature/LandingPageTest.php`, `tests/Feature/ExampleTest.php` (лише `RefreshDatabase`: `/` тепер читає `Setting`), `CLAUDE.md` (п. 5.3).
- Не чіпаємо: `ChatController`, `ChatPipeline`, `routes/chat.php`, `bootstrap/app.php`, `FaqServiceProvider`, `Setting`, `config/claude.php`, Filament, розмітку й стилі віджета (класи Tailwind, `resources/css`), конструктор `ChatRequestValidator`. Файл `data/http/chat.http` уже змінений користувачем — у цю зміну його не комітити. `openspec/specs/` вручну не правимо: його оновить архівація.
- Нові залежності: немає (ні composer, ні npm). Нових файлів і модулів немає.
- Публічний контракт: змінюється валідація `POST /chat` — довгий `answer` приймається (форма запиту й відповіді та статуси без змін).
- Автотестів на JS у проєкті немає і не додаємо (немає тестового інструментарію); логіку `chat.js` перевіряємо вручну за сценаріями `specs/chat-widget`.

## 1. Бекенд: ліміт довжини лише для питання

- [x] 1.1 Спочатку тест у `tests/Unit/Faq/ChatRequestValidatorTest.php`: прибрати кейс `long answer` з `invalidBodies`, додати тест, що `answer` довший за `maxChars` (500 при 200) приймається; кейси `long question` і `blank question` лишити. Перевірка: новий тест падає на поточному коді.
- [x] 1.2 У `app/Faq/ChatRequestValidator.php`, метод `ChatRequestValidator::text()`: перевірку `mb_strlen > $this->maxChars` виконувати лише для поля `question`; для `answer` лишається «непорожній рядок». Не додавати параметрів у конструктор, не вводити окремого `maxAnswerChars`. Перевірка: `php artisan test tests/Unit/Faq/ChatRequestValidatorTest.php` зелений.
- [x] 1.3 `php artisan test tests/Feature/Faq/ChatTest.php` зелений без змін у файлі. Кейс на `ChatTest.php:140` (`array_fill_keys(['question','answer'],'x')` → 422) лишається валідним: 422 через `answer` в останньому елементі, а не через довжину. Перевірка: тест зелений; якщо який-небудь тест очікував 422 саме на довгий `answer` — оновити його під нову поведінку.

## 2. Сторінка: ліміти з налаштувань

- [x] 2.1 Спочатку в `tests/Feature/LandingPageTest.php` додати `use RefreshDatabase` (за зразком `tests/Feature/ClaudeSettingsTest.php:12`; поведінку наявних тестів це не змінює) і новий тест: сторінка `/` містить `data-max-messages="4"` та `data-max-chars` зі значенням `config('claude.MAX_MESSAGE_CHARS')`; після `Setting::create(['key'=>'MAX_MESSAGES','value'=>'6'])` атрибут стає `6`. Перевірка: новий тест падає, наявні проходять.
- [x] 2.2 У `resources/views/components/ai-chat.blade.php` на `<div data-chat …>` додати `data-max-messages="{{ (int) \App\Models\Setting::resolve('MAX_MESSAGES') }}"` і `data-max-chars="{{ (int) \App\Models\Setting::resolve('MAX_MESSAGE_CHARS') }}"`. Іншу розмітку не чіпати. Перевірка: `php artisan test tests/Feature/LandingPageTest.php` зелений повністю.

## 3. Фронтенд: виклик /chat

Заборони для всієї фази: без нових файлів і модулів, без класу-клієнта, без i18n-обгортки; `bubble()`, розмітку, `data-chat-form` / `data-chat-input` / `data-chat-chip` не міняти.

- [x] 3.1 У `resources/js/chat.js` видалити `replies`, `fallback`, `answer()` і `setTimeout`-імітацію. `bubble`, `push`, форму, чіпи, `typing`-блокування та індикатор «Asystent pisze…» лишити за зразком наявного. Перевірка: сторінка завантажується без помилок у консолі.
- [x] 3.2 У `initChat` прочитати `maxMessages` і `maxChars` з `root.dataset`, встановити `input.maxLength = maxChars`. Перевірка: у полі не вводиться більше `maxChars` символів.
- [x] 3.3 Додати в `chat.js` функцію `buildMessages(history, question, maxMessages)`: повертає останні `maxMessages − 1` пар історії та `{question}` останнім елементом. При `maxMessages − 1 = 0` історія порожня — `slice(-0)` не використовувати (повертає весь масив). Привітальне повідомлення в історію не входить. Перевірка в консолі браузера: при `maxMessages=4` після 5 обмінів 4 елементи, останній без `answer`; при `maxMessages=1` один елемент.
- [x] 3.4 `send()` виконує `fetch('/chat', {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, body})` з тілом із `buildMessages` і розбирає відповідь: `type:"answer"` → бульбашка з `message`. Поки запит у процесі, нове повідомлення не відправляється; `typing` скидається в `finally`, індикатор прибирається завжди. Перевірка: питання з FAQ повертає відповідь з бекенду (DevTools → Network).
- [x] 3.5 Пара `{question, answer}` додається в `history` лише після успішної відповіді; `answer` не обрізається. Перевірка: друге питання відправляється з першою парою; після помилки невдале питання в наступному запиті відсутнє.
- [x] 3.6 Мапа польських повідомлень помилок — один об'єкт-константа в `chat.js` за HTTP-статусом: 429, 502, 422, інше й мережа; `message` з бекенду не показувати. Перевірка вручну: збій Claude/Pinecone → текст для 502; 11 запитів за хвилину → текст для 429; після помилки наступне питання відправляється.
- [x] 3.7 `npm run build` (`public/build` у `.gitignore`, у діф не потрапляє). Перевірка: збірка без помилок.
- [x] 3.8 Прийомка за `specs/chat-widget`: перше питання, підказка, історія, історія довша за ліміт, довге питання обрізується `maxlength`, довга відповідь іде в історії без обрізання, 429, 502, мережа. Перевірка: усі сценарії відпрацьовують, у консолі немає помилок.

## 4. Незалежна перевірка

Окремий агент із чистим контекстом; вхід — діф і `specs/`, без `decisions.md`.

- [x] 4.1 Правила проєкту: `CLAUDE.md`, `openspec/config.yaml`, Pint (`vendor/bin/pint --test`).
- [x] 4.2 Звірка зі `specs/chat-widget` і `specs/faq-chat`: кожна вимога має координату `файл:функція` у діфі.
- [x] 4.3 Помилки реалізації і хибна логіка: `maxMessages=1`, історія довша за ліміт, порожня відповідь, повторна відправка під час запиту, скидання `typing`.
- [x] 4.4 Звірка діфа з «Межами зміни»: нічого поза списком, жодного нового файлу.
- [x] 4.5 Знайдене — у роботу: виправити або завести окремою задачею.

## 5. Інтеграція

- [x] 5.1 `php artisan test` повністю зелений.
- [x] 5.2 `openspec validate update-ai-chat --strict` проходить.
- [x] 5.3 Оновити `CLAUDE.md` (розділ «FAQ chat»): описати клієнт чату на сторінці (`resources/js/chat.js`, ліміти через `data-` атрибути з `Setting::resolve`). Перевірка: опис збігається з кодом.

## Workflow follow-up

- Архівувати зміну після рев'ю (`/opsx:archive`): delta `faq-chat` оновить `openspec/specs/faq-chat/spec.md` (лише через archive), `chat-widget` створить нову специфікацію.
