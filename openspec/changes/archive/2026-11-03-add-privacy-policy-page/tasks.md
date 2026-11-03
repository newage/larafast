# Tasks

Джерела: [decisions.md](decisions.md), [design.md](design.md), `specs/`.

**Межі зміни.**
- Чіпаємо лише: `routes/web.php`, `resources/views/layouts/app.blade.php` (лише `<title>`), `resources/views/pages/privacy.blade.php` (новий), `resources/views/pages/home.blade.php` (лише смуга `.prog`, 4.3), `resources/views/components/{hero,nav,footer,cta,cta-button,ai-chat,process,services,projects,backdrop}.blade.php`, `resources/css/app.css`, `tests/Feature/LandingPageTest.php`, `tests/Feature/PrivacyPolicyTest.php` (новий).
- Не чіпаємо: `resources/js/chat.js`, `app/Faq/*`, `routes/chat.php`, `bootstrap/app.php`, `FaqServiceProvider`, `Setting`, Filament, `config/site.php` (ключ `contact_email` лишається, хоч на сторінці більше не використовується), `.agents/propose/*` (макети — лише читати), `openspec/specs/` (оновить архівація).
- Нові залежності: немає (ні composer, ні npm). Нових JS-файлів немає; нові Blade-файли — лише `pages/privacy.blade.php`.
- Публічний контракт: новий маршрут `GET /polityka-prywatnosci`; `POST /chat` не змінюється.
- Макети — референс значень: кольори, відступи й анімації беремо з inline-стилів і `<style>` макета, React-обгортку `<x-dc>`, `sc-if`/`sc-for` і скрипти макета не переносимо.
- Автотестів на CSS-анімації немає і не додаємо; їх перевіряємо вручну в браузері за сценаріями `specs/landing-page`.

## 1. Сторінка політики

- [x] 1.1 Спочатку тест `tests/Feature/PrivacyPolicyTest.php` (за зразком `tests/Feature/LandingPageTest.php`): `GET /polityka-prywatnosci` → 200, `<html lang="pl"`, `<title>Polityka prywatności</title>`, у тексті «Vadym Leontiev», `hello@vadymleontiev.dev`, «netcup», «Google», «30 dni», посилання «Strona główna» і «Wróć do strony głównej» з `href="/"`; текст не збігається з `/\[[A-ZŁ ]+[^\]]*\]/u` (жодних плейсхолдерів у квадратних дужках). Перевірка: тест падає на поточному коді (404).
- [x] 1.2 У `resources/views/layouts/app.blade.php` замінити `<title>Strony www i automatyzacja AI</title>` на `<title>@yield('title', 'Strony www i automatyzacja AI')</title>` і значення `<meta name="description" content="…">` на `@yield('description', '<наявний текст>')`; наявні тексти лишити значеннями за замовчуванням. Перевірка: `php artisan test tests/Feature/LandingPageTest.php` зелений без змін у файлі.
- [x] 1.3 У `routes/web.php` додати `Route::view('/polityka-prywatnosci', 'pages.privacy');` після наявного маршруту `/`. Не створювати контролер. Перевірка: `php artisan route:list` показує маршрут.
- [x] 1.4 Створити `resources/views/pages/privacy.blade.php` на `layouts.app` за зразком `pages/home.blade.php`: `@section('title', 'Polityka prywatności')` і `@section('description', 'Polityka prywatności: jakie dane przetwarzam na stronie i w czacie z asystentem AI.')`, навігація (ім'я + кнопка «Strona główna» → `/`), 10 розділів із `.agents/propose/polityka-html/Polityka.dc.html` з уже підставленими даними (п. 1: «Administratorem danych jest Vadym Leontiev, kontakt: hello@vadymleontiev.dev»; п. 5: хостинг «netcup», пошта «Google (Gmail)», модель AI «Anthropic»; п. 7: «30 dni» для розмов і логів; п. 8: `hello@vadymleontiev.dev`), футер «© {{ now()->year }} Vadym Leontiev» + «Wróć do strony głównej» → `/`. Значення стилів брати з макета; без плейсхолдерів. Дату «Ostatnia aktualizacja: 3 listopada 2026» лишити з макета. Не виносити в окремі `x-` компоненти — один файл: макет має власну мінімальну навігацію, повторного використання немає. Перевірка: `php artisan test tests/Feature/PrivacyPolicyTest.php` зелений.

## 2. Головна: посилання, CTA, футер, повідомлення біля чату

- [x] 2.1 Спочатку оновити `tests/Feature/LandingPageTest.php`: `test_calls_to_action_lead_to_contact_and_mailto` → перейменувати й змінити на «рівно 3 CTA («Czat z AI», «Czat z asystentem» ×2) з `href="#chat"`, жодної кнопки з `mailto:`; у футері є `href="mailto:hello@vadymleontiev.dev"`» (рядок `config(['site.contact_email' => …])` зі старого тесту видалити); `test_page_has_no_internal_links_except_anchors` — додатково дозволити `/polityka-prywatnosci`; `test_page_has_technologies_chat_chips_and_footer` — замінити `assertSee('Regulamin')` на `assertDontSee('Regulamin')`; новий тест: футер і чат містять `href="/polityka-prywatnosci"`, текст «Rozmawiasz z asystentem AI. Nie podawaj danych wrażliwych.», заголовок секції контакту «Opisz, co spowalnia Twoją firmę.». Перевірка: нові/змінені тести падають, решта проходить.
- [x] 2.2 У `resources/views/components/cta-button.blade.php` змінити `'href' => '#contact'` на `'#chat'`. Перевірка: рядок містить `#chat`.
- [x] 2.3 Змінити підписи й лінки: `nav.blade.php` — «Umów rozmowę» → «Czat z AI»; `hero.blade.php` — «Umów bezpłatną rozmowę» → «Czat z asystentem»; у `ai-chat.blade.php` видалити `<x-cta-button>Umów bezpłatną rozmowę</x-cta-button>` разом із його обгорткою `<div class="mt-2">`. Не чіпати інші атрибути й розмітку чату. Перевірка: `grep -rn "Umów" resources/views` показує лише `cta.blade.php` (його править 2.4).
- [x] 2.4 У `resources/views/components/cta.blade.php` прибрати `:href="'mailto:'.config('site.contact_email')"` (кнопка бере `#chat` за замовчуванням), підпис кнопки — «Czat z asystentem», замінити заголовок на «Opisz, co spowalnia Twoją firmę.» і абзац на «Napisz do asystenta AI w czacie, co chcesz uprościć. Jeśli oprogramowanie nie jest właściwą odpowiedzią, usłyszysz to wprost.»; примітку «Bez zobowiązań i bez nachalnych przypomnień.» лишити. Перевірка: `grep -rn "Umów" resources/views` порожній.
- [x] 2.5 У `footer.blade.php` замінити два `<span>` на `<a href="mailto:hello@vadymleontiev.dev">hello@vadymleontiev.dev</a>` і `<a href="/polityka-prywatnosci">Polityka prywatności</a>` зі стилями `.foot a` з макета (`#9B9B9B`, hover `#FFFFFF`); «Regulamin» видалити. Перевірка: `php artisan test tests/Feature/LandingPageTest.php` зелений.
- [x] 2.6 У `ai-chat.blade.php` між блоком чіпів (`data-chat-chip`) і `<form data-chat-form>` додати блок «Rozmawiasz z asystentem AI. Nie podawaj danych wrażliwych. <a href="/polityka-prywatnosci">Polityka prywatności</a>» за значеннями макета (рядок 394 `Main.dc.html`). Атрибути `data-chat*` і класи форми не змінювати. Перевірка: тест із 2.1 зелений; `php artisan test` повністю зелений.

## 3. Hero-блок: нові кроки

- [x] 3.1 Спочатку тест у `LandingPageTest.php`: `assertSeeInOrder` для чотирьох нових кроків і позначок («Ustalam z klientem, czego potrzebuje» … «Rozmowa» … «Akceptacja» … «Kod» … «Online»), шапки «Przykładowy proces» / «Jak pracuję», та `assertDontSee('Zapytanie klienta')`, `assertDontSee('Czeka na Ciebie')`, `assertDontSee('Gotowe')`. Перевірка: тест падає.
- [x] 3.2 У `hero.blade.php` замінити масив `$steps` на чотири пари з `specs/landing-page` (текст, позначка); у шапці блоку «Zapytanie klienta» → «Jak pracuję»; клас `text-ink` для позначки лишити лише на останньому кроці (`$loop->last`), як зараз. Не міняти структуру циклу й решту hero. Перевірка: тест із 3.1 і `LandingPageTest` зелені.

## 4. Анімації макета

Заборони для фази: не додавати JS (усе на CSS), не змінювати наявні класи `.in`, `.reveal`, `.w`, `.msg`; значення тривалостей і easing — з `<style>` у `Main.dc.html`.

- [x] 4.1 У `resources/css/app.css` додати правила й `@keyframes` з `<style>` макета: `.wf`/`.wfbar`/`wfbar`/`wfrow` (затримки 3s/6s/9s для рядків 4–6 блоку), `.live`/`livep`, `.prog`/`prog`, `.dots circle`/`floaty`, `.draw`/`draw`, `.grow`/`grow`, `.spark`/`sparkp`, `.mq`/`.mqi`/`mq`, `.card svg` hover; `@supports not (animation-timeline: scroll())` — запас лише для `.prog` (ховається) за зразком `.reveal`; `.wf`/`.wfbar`/`.mq` працюють за часом і запасу не потребують. Додати всі нові класи в `@media (prefers-reduced-motion: reduce)` до `animation: none`. Перевірка: `npm run build` без помилок.
- [x] 4.2 У `hero.blade.php`: `wf` на контейнері блоку (рядок 181 макета), `<div class="wfbar" aria-hidden="true"></div>` першим дитям, `<span class="live" aria-hidden="true"></span>` перед «Przykładowy proces». Перевірка: `php artisan test tests/Feature/LandingPageTest.php` зелений.
- [x] 4.3 У `pages/home.blade.php` додати `<div class="prog" aria-hidden="true"></div>` одразу після посилання `<a href="#main" …>` (рядок 7). Решту файлу не чіпати. Перевірка: тест `test_home_page_has_all_sections_in_order` зелений.
- [x] 4.4 У `backdrop.blade.php` додати `<g class="dots">` з кружками з макета (рядок 135 `Main.dc.html`), не змінюючи наявні `spin1/2/3`, `pulse`. Перевірка: сторінка рендериться, тести зелені.
- [x] 4.5 У `process.blade.php` замінити лише обгортку `flex-wrap` із технологіями (рядок 43) на `.mq` з двома `.mqi` (другий з `aria-hidden="true"`). Масив `$technologies`, порядок і текст `<span>` не чіпати. Перевірка: `test_page_has_technologies_chat_chips_and_footer` зелений (порядок технологій).
- [x] 4.6 У `services.blade.php` і `projects.blade.php` додати класи `card`, `draw`, `grow`, `spark` на SVG і картках так, як у макеті (рядки 223–290 `Main.dc.html`). Кількість `data-card="service|project"` не змінюється. Перевірка: `test_home_page_card_counts` зелений.
- [x] 4.7 У `ai-chat.blade.php` додати `<span class="live" aria-hidden="true"></span>` у підпис «Odpowiada na pytania…» (рядок 370 макета). Стан «Asystent pisze…» і логіку чату з макета не переносити; `data-chat*` не чіпати. Перевірка: тести зелені.
- [x] 4.8 Ручна перевірка в браузері (`composer dev`): hero підсвічує кроки циклом зі смугою зверху; індикатор «live» пульсує; смуга скролу росте; марквіз технологій біжить; з `prefers-reduced-motion: reduce` усе статично й видимо; на ширині 375px немає горизонтального скролу. Перевірка: пройти список і дописати результат (що перевірено, що ні) у відповідь виконавця.

## 5. Незалежна перевірка

Виконує окремий агент без контексту реалізації (скіл `dev-flow:review`).

- [x] 5.1 Рев'ю за правилами `CLAUDE.md` і `vendor/bin/pint --test`. Перевірка: висновок без зауважень або з пунктами в 5.6.
- [x] 5.2 Звірка кожної вимоги з `specs/*` із координатою `файл:місце` у коді. Перевірка: таблиця «вимога → файл:місце».
- [x] 5.3 Краї: порожні й граничні значення, старі тести, що могли змінити зміст. Перевірка: перелік перевіреного.
- [x] 5.4 Хибна логіка: кожна CTA веде на `#chat`, чат працює, кнопок з `mailto:` немає (лише email у футері), політика не містить `[…]`. Перевірка: висновок з конкретними фактами.
- [x] 5.5 `git diff --name-only` проти «Меж зміни». Перевірка: жодного файлу поза межами.
- [x] 5.6 Знайдене завести окремими пунктами нижче й закрити.

## 6. Закриття

- [x] 6.1 `php artisan test` повністю зелений; `vendor/bin/pint --test` без зауважень; `npm run build` успішний. Перевірка: усі три команди завершуються кодом 0.
- [x] 6.2 Вручну: клік на «Czat z AI»/«Czat z asystentem» гортає до `#chat`; «Polityka prywatności» у футері й під чатом відкриває `/polityka-prywatnosci`; «Strona główna» повертає на `/`; чат надсилає питання як раніше. Перевірка: пройти список і дописати результат у відповідь виконавця.

## Workflow follow-up

- Архівувати зміну через `/opsx:archive` після рев'ю (створить `openspec/specs/{privacy-policy-page,landing-page}` і оновить `chat-widget`).
- Окремо: юридична вичитка політики; окрема зміна для зберігання питань без відповіді.
