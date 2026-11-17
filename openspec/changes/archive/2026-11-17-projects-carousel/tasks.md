# Tasks

Межі: чіпаємо лише `resources/views/components/projects.blade.php`, `resources/css/app.css`, `resources/js/app.js`. Не чіпати тексти наявних плиток (зокрема теги vadymleontiev.dev), бекенд, `package.json`.

## 1. Дані і розмітка

- [x] 1.1 У `projects.blade.php` додати Nordleaf першим елементом `$projects` (meta «w trakcie · sklep roślin», теги `astro · typescript · decap cms`, url `https://nordleaf.vadymleontiev.dev/`, host `nordleaf.vadymleontiev.dev`, опис польською з `.agents/propose/carousel.md`); перевірити: у браузері Nordleaf першою.
- [x] 1.2 У тому ж файлі обгорнути `.projects` у контейнер каруселі й додати кнопки `<button>` «Poprzednie»/«Następne» з `aria-label`; перевірити: кнопки в DOM, доступні Tab-ом.

## 2. Стилі

- [x] 2.1 У `app.css` (біля `:224`) замінити grid `.projects` на flex-ряд з `overflow-x:auto` і `scroll-snap`, плитки `.proj` з `flex-basis` 1/3; перевірити: 4 плитки на десктопі, видно 3, четверта скролом.
- [x] 2.2 Оновити адаптив у `app.css` (`:316`, `:328`): 2 і 1 плитка в ряду замість колонок grid; перевірити в devtools на ~900px і ~400px.
- [x] 2.3 Додати стилі кнопок і стану `disabled` за зразком наявних кнопок; перевірити візуально в світлій/темній темі, якщо є обидві.

## 3. Поведінка

- [x] 3.1 У `app.js` додати логіку: клік зсуває ряд на ширину плитки (`scrollBy`), `disabled` на краях за `scroll`/`resize` з допуском у кілька px; перевірити: «Poprzednie» неактивна на початку, «Następne» у кінці.
- [x] 3.2 Зібрати фронтенд (`npm run build` у контейнері node) і пройти сценарії зі спеки `landing-page` (карусель, стрілки, порядок плиток); перевірити: без помилок у консолі.
