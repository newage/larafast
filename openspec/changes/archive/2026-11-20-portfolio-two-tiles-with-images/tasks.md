# Tasks

## 1. Ресурси

- [x] 1.1 Скопіювати `nordleaf.jpg`, `englishdom.jpg`, `allrightpro.jpg`, `vadymleontiev.dev.jpg` з `.agents/propose/portfolio/` у `public/images/portfolio/` (оригінали не видаляти). Перевірка: `ls public/images/portfolio` показує 4 файли.

## 2. Верстка

- [x] 2.1 У `resources/views/components/projects.blade.php` (масив `$projects`, цикл `@foreach`) додати шлях зображення до кожного проєкту; першим елементом `<article class="proj">` вивести `<img src="{{ asset(...) }}" alt="{{ $name }}" width="751" height="477" loading="lazy">`, решту вмісту обгорнути в `div` для відступів. Не чіпати тексти, порядок, `data-carousel*`. Перевірка: сторінка рендериться, у кожній плитці є `<img>` з `alt`.
- [x] 2.2 У `resources/css/app.css` (блок `.proj`, рядок ~233): десктопний `flex-basis` → `calc((100% - var(--s4)) / 2)`; прибрати padding з `.proj`, додати `overflow:hidden`, стилі для зображення (`display:block;width:100%;height:auto`) і відступ `var(--s5)` для текстової обгортки. Правила в `@media (max-width:960px)` і `(max-width:720px)` не змінювати. Перевірка: `npm run build` без помилок.

## 3. Перевірка

- [x] 3.1 Відкрити лендинг: на десктопі видно 2 плитки зі скріншотами на всю ширину, стрілки гортають по одній плитці, на ≤960px 2, на ≤720px 1. Перевірка: ручний огляд на трьох ширинах.
- [x] 3.2 Пройтися `php artisan test` (за наявності тестів, що перевіряють розмітку лендингу) і оновити їх, якщо вони залежать від структури `.proj`. Перевірка: тести зелені.
