# landing-page Specification

## Purpose

Описує поведінку головної сторінки сайту в частині закликів до дії, футера з посиланням на політику та hero-блоку з прикладом процесу й анімаціями.

## Requirements

### Requirement: Футер із посиланням на політику
Футер головної SHALL містити «© <поточний рік> Vadym Leontiev», адресу `hello@vadymleontiev.dev` як посилання `mailto:` і посилання «Polityka prywatności» на `/polityka-prywatnosci`. Пункту «Regulamin» SHALL NOT бути.

#### Scenario: Футер
- **WHEN** відвідувач прокручує головну до кінця
- **THEN** бачить посилання «Polityka prywatności» з `href="/polityka-prywatnosci"`, посилання `href="mailto:hello@vadymleontiev.dev"` і не бачить «Regulamin»

### Requirement: Дзвінок на 15 хвилин через Google Calendar
Кнопка «Umów 15 minut rozmowy» SHALL вести (`<a href>`) на публічну сторінку запису Google Calendar `https://calendar.app.google/xVjCjasQxYJgKxyN9` і відкривати її в новій вкладці. Кнопка SHALL NOT вести на `mailto:`.

#### Scenario: Кнопка запису
- **WHEN** відвідувач натискає «Umów 15 minut rozmowy»
- **THEN** відкривається сторінка запису Google Calendar у новій вкладці

#### Scenario: Адресу змінено через env
- **WHEN** у середовищі задано інший URL запису
- **THEN** кнопка веде на нього без зміни коду

### Requirement: Копіювання email
Натискання на «hello@vadymleontiev.dev · kliknij, aby skopiować» SHALL копіювати адресу в буфер обміну й на час показувати «skopiowano». Якщо Clipboard API недоступний, SHALL використовуватися запасний спосіб; якщо копіювання неможливе, SHALL показуватися підказка виділити адресу вручну.

#### Scenario: Успішне копіювання
- **WHEN** відвідувач натискає на адресу
- **THEN** буфер містить `hello@vadymleontiev.dev`, а підпис змінюється на «skopiowano»

#### Scenario: Небезпечний контекст
- **WHEN** `navigator.clipboard` недоступний
- **THEN** адреса копіюється запасним способом

### Requirement: Секція контакту з формою
Секція контакту SHALL мати заголовок «Masz proces, który zjada czas Twojego zespołu?», форму запиту (спека `contact-form`) з кнопкою «Wyślij» і посилання на політику приватності.

#### Scenario: Вигляд секції
- **WHEN** відвідувач відкриває секцію контакту
- **THEN** бачить заголовок, форму з кнопкою «Wyślij», кнопку «Umów 15 minut rozmowy» і адресу для копіювання

### Requirement: Карусель реалізацій
Плитки проєктів у розділі «Realizacje» SHALL відображатися горизонтальним рядом, що прокручується (свайп, коліщатко, клавіатурний фокус), із прилипанням до країв плиток. На десктопі SHALL бути видно 3 плитки, на планшеті 2, на мобільному 1. Кількість плиток у рядку SHALL NOT обмежуватися.

#### Scenario: Більше плиток, ніж влазить
- **WHEN** у розділі 4 плитки, а ширина екрана десктопна
- **THEN** видно 3 плитки, четверта доступна прокруткою, а розділ не росте вниз

#### Scenario: Мобільний екран
- **WHEN** ширина екрана мобільна
- **THEN** у рядку видно одну плитку, інші доступні свайпом

### Requirement: Стрілки каруселі
Біля каруселі SHALL бути кнопки «Poprzednie» і «Następne», що зсувають рядок на одну плитку. На початку рядка перша кнопка SHALL бути неактивною, у кінці друга. Кнопки SHALL бути доступні з клавіатури.

#### Scenario: Прокрутка стрілкою
- **WHEN** відвідувач натискає «Następne» на початку рядка
- **THEN** рядок зсувається на одну плитку, а «Poprzednie» стає активною

#### Scenario: Кінець рядка
- **WHEN** остання плитка повністю видна
- **THEN** кнопка «Następne» неактивна

### Requirement: Порядок плиток і Nordleaf
Першою плиткою SHALL бути Nordleaf з посиланням `https://nordleaf.vadymleontiev.dev/`, технологіями Astro, TypeScript, Decap CMS і позначкою «w trakcie» у рядку `meta`. Далі йдуть наявні плитки в поточному порядку.

#### Scenario: Перша плитка
- **WHEN** відвідувач відкриває «Realizacje»
- **THEN** першою бачить Nordleaf із позначкою «w trakcie» і посиланням, що відкривається в новій вкладці
