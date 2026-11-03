# Proposal

Джерело рішень: [decisions.md](decisions.md).

## Why

Політика приватності є лише в макеті: на сайті «Polityka prywatności» у футері — `<span>` без посилання, а біля чату немає попередження про обробку даних, хоча повідомлення відвідувача йдуть до AI-провайдера. Водночас оновлений макет головної вже відрізняється від коду: інші кроки й анімація в hero-блоці, нові анімації, інші підписи CTA. Єдиним каналом зв'язку має стати чат.

## What Changes

- Нова сторінка політики приватності `GET /polityka-prywatnosci` (статична, польською, з власною навігацією за макетом) з реальними даними адміністратора: Vadym Leontiev, `hello@vadymleontiev.dev`, хостинг netcup, пошта Google (Gmail), строк зберігання 30 днів.
- Футер головної: «© рік», email `hello@vadymleontiev.dev` (посилання `mailto:`) і посилання на політику; «Regulamin» прибрано.
- Під полем вводу чату — рядок «Rozmawiasz z asystentem AI. Nie podawaj danych wrażliwych.» з посиланням на політику. Логіка чату не змінюється.
- Hero-блок «Przykładowy proces»: 4 нові кроки створення сайту з позначками Rozmowa / Akceptacja / Kod / Online; права частина шапки — «Jak pracuję».
- Усі кнопки-заклики ведуть на `#chat`: nav — «Czat z AI», hero і секція контакту — «Czat z asystentem». Кнопка в секції чату видаляється. кнопки з `mailto:` зникають; текст секції контакту переписано під чат.
- На головну переносяться всі анімації макета, яких бракує: `.wf`/`.wfbar`, `.live`, `.prog`, `.dots`, `.mq`, `.draw`, `.grow`, `.spark`, hover карток.
- Тести `LandingPageTest` (заборона внутрішніх лінків, «Regulamin», кількість CTA й `mailto`) оновлюються.

## Capabilities

### New Capabilities
- `privacy-policy-page`: сторінка політики приватності на окремому маршруті, її вміст-мінімум і навігація назад на головну.
- `landing-page`: поведінка головної в частині цієї зміни — CTA на чат, футер із посиланням на політику, hero-блок із кроками й анімаціями.

### Modified Capabilities
- `chat-widget`: додається вимога про повідомлення про обробку даних із посиланням на політику під чатом.

## Impact

- `routes/web.php` — новий маршрут; нова view `resources/views/pages/privacy.blade.php` (+ компоненти навігації/футера політики).
- `resources/views/components/{hero,nav,footer,cta,ai-chat,process}.blade.php`, `resources/css/app.css`.
- `tests/Feature/LandingPageTest.php`, новий тест сторінки політики.
- Без змін: `chat.js`, `app/Faq/*`, `routes/chat.php`, адмінка, `config/site.php` (ключ `contact_email` лишається, але на сторінці більше не використовується).
- Поза обсягом: юридична вичитка тексту політики, зберігання питань без відповіді.
