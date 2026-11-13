# Spec Delta

## ADDED Requirements

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

## REMOVED Requirements

### Requirement: Заклики до дії ведуть на чат
**Reason**: Новий дизайн веде заклики до дзвінка в Google Calendar, а контакт відбувається через форму.
**Migration**: Див. вимоги «Дзвінок на 15 хвилин через Google Calendar» і «Секція контакту з формою».

### Requirement: Секція контакту відповідає чату
**Reason**: Секція контакту тепер містить форму, а не пропозицію написати асистентові.
**Migration**: Див. «Секція контакту з формою».

### Requirement: Hero-блок із процесом створення сайту
**Reason**: Новий макет має інший hero й блок «jak» з власним вмістом.
**Migration**: Вміст hero визначається макетом `.agents/propose/index.html`.

### Requirement: Анімації макета
**Reason**: Набір анімацій змінюється разом із макетом; поведінка `prefers-reduced-motion` зберігається в дизайні.
**Migration**: Нові анімації за макетом; за `prefers-reduced-motion: reduce` вони вимкнені.
