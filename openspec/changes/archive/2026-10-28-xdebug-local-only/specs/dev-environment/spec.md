# Spec Delta

## Purpose
Описує, як Docker-образ php залежить від оточення `APP_ENV`, зокрема коли в ньому присутній xdebug.

## ADDED Requirements

### Requirement: Xdebug лише для локального оточення
Образ php SHALL містити встановлений і ввімкнений xdebug тільки коли `APP_ENV` дорівнює `local`. Для будь-якого іншого значення `APP_ENV` образ SHALL збиратися без xdebug і без його конфігурації. Якщо `APP_ENV` не задано, SHALL діяти значення `local`.

#### Scenario: Локальне оточення
- **WHEN** образ збирається з `APP_ENV=local`
- **THEN** `php -m` у контейнері містить `xdebug`, а `xdebug.ini` присутній у `conf.d`

#### Scenario: Прод-оточення
- **WHEN** образ збирається з `APP_ENV=prod`
- **THEN** `php -m` не містить `xdebug`, а `xdebug.ini` у `conf.d` відсутній

#### Scenario: Інше значення оточення
- **WHEN** образ збирається з `APP_ENV=staging`
- **THEN** xdebug не встановлений

#### Scenario: APP_ENV не задано
- **WHEN** образ збирається без `APP_ENV`
- **THEN** xdebug встановлений, як для `local`
