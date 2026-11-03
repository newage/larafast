# Proposal

Джерело рішень: [decisions.md](decisions.md).

## Why

Чат на сторінці — заглушка: `resources/js/chat.js` відповідає за зашитими ключовими словами й не звертається до `POST /chat`. Відвідувач бачить вигадані відповіді замість відповідей з FAQ, а готовий бекенд не використовується. Крім того, `MAX_MESSAGE_CHARS` задуманий як ліміт питання клієнта, але бекенд застосовує його й до `answer`, тож довга відповідь Claude в історії ламала б наступний запит (422).

## What Changes

- Чат на сторінці надсилає повідомлення на `POST /chat` і показує `message` з відповіді `type: "answer"`.
- Питання користувача йде останнім елементом `messages` (лише `question`); перед ним — історія завершених пар «питання → відповідь», не більше `MAX_MESSAGES − 1`.
- Помилки (`type: "error"`, мережа) показуються польським текстом залежно від HTTP-статусу (429, 502, 422, інше); англійський `message` з бекенду користувачу не показується.
- Поле вводу обмежене `MAX_MESSAGE_CHARS` (`maxlength`); `MAX_MESSAGES` і `MAX_MESSAGE_CHARS` беруться з налаштувань (адмінка, інакше `.env`) і передаються сторінці сервером.
- **Зміна поведінки бекенду:** `MAX_MESSAGE_CHARS` обмежує лише `question`; довжина `answer` більше не перевіряється (порожній `answer` усе ще відхиляється).

## Capabilities

### New Capabilities
- `chat-widget`: поведінка чату на сторінці — відправка на `/chat`, накопичення й обрізання історії за `MAX_MESSAGES`, ліміт довжини питання, показ відповідей і польських повідомлень про помилки.

### Modified Capabilities
- `faq-chat`: вимога «Валідація запиту» — `MAX_MESSAGE_CHARS` застосовується лише до `question`, довгий `answer` приймається.

## Impact

- `resources/js/chat.js` — заміна заглушки на виклик `/chat`.
- `resources/views/components/ai-chat.blade.php` — `data-` атрибути з `MAX_MESSAGES` і `MAX_MESSAGE_CHARS` (через `Setting::resolve`).
- `app/Faq/ChatRequestValidator.php` та `tests/Unit/Faq/ChatRequestValidatorTest.php` — довжина `answer` не перевіряється.
- `tests/Feature/LandingPageTest.php` — перевірка атрибутів.
- `openspec/specs/faq-chat/spec.md` (через delta). `ChatController`, `ChatPipeline`, `routes/chat.php` не змінюються.
