# Spec Delta

## Purpose

Описує окремий cron-контейнер, що регулярно запускає планувальник Laravel (`schedule:run`) за розкладом, обраним відповідно до `APP_ENV`.

## ADDED Requirements

### Requirement: Окремий cron-контейнер
Стек docker SHALL містити окремий сервіс `cron`, який виконує розклад незалежно від php-fpm і nginx. Сервіс SHALL працювати від користувача `app` і виводити лог виконання в stdout контейнера.

#### Scenario: Запуск стека
- **WHEN** виконано `make up`
- **THEN** сервіс `cron` запущений окремим контейнером поруч із `php`

#### Scenario: Лог виконання
- **WHEN** cron запускає команду з розкладу
- **THEN** її вивід і статус видно в `docker logs` контейнера `cron`

### Requirement: Розклад за оточенням
Сервіс `cron` SHALL використовувати файл розкладу `docker/crond/<APP_ENV>`. Якщо `APP_ENV` не задано, SHALL діяти значення `local`. Файли розкладу SHALL мати нативний формат crontab.

#### Scenario: Прод-оточення
- **WHEN** `APP_ENV=prod`
- **THEN** cron виконує розклад із `docker/crond/prod`, де `php artisan schedule:run` запускається кожні 5 хвилин (`*/5 * * * *`)

#### Scenario: Локальне оточення
- **WHEN** `APP_ENV=local`
- **THEN** cron виконує розклад із `docker/crond/local`

#### Scenario: APP_ENV не задано
- **WHEN** `APP_ENV` не задано
- **THEN** cron виконує розклад із `docker/crond/local`

### Requirement: Доступ до оточення застосунку
Команди розкладу SHALL виконуватися з робочою директорією застосунку та з тими самими змінними оточення, що й php-контейнер.

#### Scenario: Запуск планувальника
- **WHEN** cron виконує `php artisan schedule:run`
- **THEN** команда запускається в директорії застосунку, підключається до бази даних і виконує заплановані команди з `routes/console.php`
