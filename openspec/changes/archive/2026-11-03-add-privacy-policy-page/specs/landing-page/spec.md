# Spec Delta

## Purpose

Описує поведінку головної сторінки сайту в частині закликів до дії, футера з посиланням на політику та hero-блоку з прикладом процесу й анімаціями.

## ADDED Requirements

### Requirement: Заклики до дії ведуть на чат
Усі кнопки-заклики на головній SHALL вести на якір `#chat`. У навігації кнопка SHALL називатися «Czat z AI», у hero-блоці та секції контакту — «Czat z asystentem». Секція чату SHALL NOT мати власної кнопки-заклику. Жодна кнопка-заклик SHALL NOT вести на `mailto:`.

#### Scenario: Кнопки в nav, hero і контакті
- **WHEN** відвідувач відкриває головну
- **THEN** є рівно три кнопки-заклики («Czat z AI», «Czat z asystentem» двічі), кожна з `href="#chat"`

#### Scenario: Кнопки без mailto
- **WHEN** головна відрендерена
- **THEN** жодна кнопка-заклик не має `href="mailto:…"`

### Requirement: Секція контакту відповідає чату
Секція `#contact` SHALL пропонувати написати асистентові AI в чаті, а не умовляти про розмову чи дзвінок.

#### Scenario: Текст секції контакту
- **WHEN** відвідувач бачить секцію `#contact`
- **THEN** заголовок — «Opisz, co spowalnia Twoją firmę.», а абзац згадує асистента AI в чаті

### Requirement: Футер із посиланням на політику
Футер головної SHALL містити «© <поточний рік> Vadym Leontiev», адресу `hello@vadymleontiev.dev` як посилання `mailto:` і посилання «Polityka prywatności» на `/polityka-prywatnosci`. Пункту «Regulamin» SHALL NOT бути.

#### Scenario: Футер
- **WHEN** відвідувач прокручує головну до кінця
- **THEN** бачить посилання «Polityka prywatności» з `href="/polityka-prywatnosci"`, посилання `href="mailto:hello@vadymleontiev.dev"` і не бачить «Regulamin»

### Requirement: Hero-блок із процесом створення сайту
Hero-блок «Przykładowy proces» SHALL мати шапку «Przykładowy proces» ліворуч і «Jak pracuję» праворуч та 4 нумеровані кроки в порядку: «Ustalam z klientem, czego potrzebuje» — «Rozmowa»; «Przygotowuję projekt graficzny i logo, klient je zatwierdza» — «Akceptacja»; «Tworzę stronę na podstawie zatwierdzonego projektu» — «Kod»; «Publikuję stronę na hostingu» — «Online».

#### Scenario: Кроки й позначки
- **WHEN** відвідувач бачить hero-блок
- **THEN** кроки 01–04 мають зазначені тексти й позначки, а «Zapytanie klienta», «Gotowe», «Czeka na Ciebie» відсутні

### Requirement: Анімації макета
Головна SHALL відтворювати анімації макета: послідовне підсвічування кроків hero-блоку зі смугою прогресу, індикатор «live», смугу прогресу скролу, марквіз технологій та декоративні анімації (`dots`, `draw`, `grow`, `spark`). За `prefers-reduced-motion: reduce` анімації SHALL бути вимкнені.

#### Scenario: Підсвічування кроків
- **WHEN** hero-блок видимий
- **THEN** кроки по черзі підсвічуються циклом, а над блоком біжить смуга прогресу

#### Scenario: Зменшений рух
- **WHEN** користувач увімкнув `prefers-reduced-motion: reduce`
- **THEN** анімації не відтворюються, а контент лишається видимим
