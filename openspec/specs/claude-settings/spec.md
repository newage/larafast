# claude-settings Specification

## Purpose
Дає адміністратору змінювати параметри Claude-чату з адмінки без доступу до сервера, зберігаючи `.env` як джерело дефолтів.

## Requirements

### Requirement: Setup page in admin
The system SHALL provide a "Setup" page in the admin panel, in Polish, available only to authenticated admin users.

#### Scenario: Authenticated admin opens Setup
- **WHEN** an authenticated admin opens the Setup page
- **THEN** the page shows editable fields for all seven Claude parameters with Polish labels

#### Scenario: Guest opens Setup
- **WHEN** an unauthenticated visitor requests the Setup page
- **THEN** they are redirected to the admin login

### Requirement: Settings override environment defaults
The system SHALL resolve each Claude parameter from the stored setting when it has a non-empty value, and otherwise from the `.env` default.

#### Scenario: Stored value wins
- **WHEN** `CLAUDE_MAX_TOKENS` is 1024 in `.env` and 2048 is saved in Setup
- **THEN** the effective value is 2048

#### Scenario: Empty value falls back
- **WHEN** the saved value for `CLAUDE_MAX_TOKENS` is cleared in Setup
- **THEN** the effective value is the `.env` value 1024

### Requirement: Parameter validation
The system SHALL reject invalid values on save: `CLAUDE_EFFORT` must be empty or one of `low`, `medium`, `high`, `xhigh`, `max`; `CLAUDE_MAX_TOKENS`, `MAX_MESSAGES` and `MAX_MESSAGE_CHARS` must be positive integers; `VECTOR_MIN_SCORE` must be a number from 0 to 1.

#### Scenario: Score out of range
- **WHEN** the admin saves `VECTOR_MIN_SCORE` as 1.5
- **THEN** the save is rejected with a validation message and nothing is stored

### Requirement: API key is stored encrypted and masked
The system SHALL store `ANTHROPIC_API_KEY` encrypted, show only a masked form of it on the Setup page, replace it when a new value is submitted, and keep it when the field is left empty.

#### Scenario: Key is masked
- **WHEN** a key ending in `x7Qf` is saved and the admin reopens Setup
- **THEN** the form shows a masked value such as `sk-ant-…x7Qf`, never the full key

#### Scenario: Empty field keeps key
- **WHEN** the admin saves the form with the key field empty
- **THEN** the previously saved key is unchanged

#### Scenario: Stored value is not plaintext
- **WHEN** a key is saved
- **THEN** the raw value in the database differs from the submitted key
