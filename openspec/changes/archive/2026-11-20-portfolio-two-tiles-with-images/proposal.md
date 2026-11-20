# Proposal

## Why

Секція «Realizacje» показує текстові картки без візуалу, по 3 у видимій зоні. Відвідувач не бачить, як виглядають проєкти, тому довіра до кейсів слабша. Деталі проблеми й рішень: `decisions.md`.

## What Changes

- Карусель «Realizacje»: на десктопі видно 2 плитки замість 3 (планшет 2, мобільний 1 без змін).
- Кожна плитка отримує зображення-скріншот на всю ширину плитки зверху, текстовий блок лишається під ним.
- Чотири скріншоти з `.agents/propose/portfolio/` переносяться в `public/images/portfolio/`.
- Тексти, порядок і кількість плиток (4) не змінюються.

## Capabilities

### New Capabilities
<!-- немає -->

### Modified Capabilities
- `landing-page`: вимога «Карусель реалізацій» (2 плитки на десктопі) і нова вимога про зображення в плитці.

## Impact

- `resources/views/components/projects.blade.php`, `resources/css/app.css`, нові файли `public/images/portfolio/*.jpg`.
- JS каруселі (`resources/js/app.js`), бекенд, залежності не змінюються.
