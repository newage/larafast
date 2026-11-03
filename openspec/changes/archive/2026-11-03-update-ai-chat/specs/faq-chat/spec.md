# Spec Delta

## MODIFIED Requirements

### Requirement: Валідація запиту
Сервіс SHALL відхиляти запит з HTTP 422 і тілом `{"type":"error","message":"..."}`, якщо: `messages` порожній або довший за `MAX_MESSAGES`; будь-який `question` довший за `MAX_MESSAGE_CHARS` або порожній; будь-який `answer` порожній; останній елемент має `answer`; не останній елемент не має `answer`. Довжина `answer` SHALL NOT перевірятися.

#### Scenario: Забагато повідомлень
- **WHEN** `MAX_MESSAGES=4` і клієнт надсилає 5 елементів
- **THEN** сервіс відповідає HTTP 422 з `type` рівним `error`

#### Scenario: Занадто довге питання
- **WHEN** `MAX_MESSAGE_CHARS=200` і `question` має 201 символ
- **THEN** сервіс відповідає HTTP 422 з `type` рівним `error`

#### Scenario: Довга відповідь в історії
- **WHEN** `MAX_MESSAGE_CHARS=200`, а `answer` у попередньому елементі має 500 символів
- **THEN** сервіс приймає запит і обробляє його

#### Scenario: Останнє повідомлення з відповіддю
- **WHEN** останній елемент `messages` містить `answer`
- **THEN** сервіс відповідає HTTP 422 з `type` рівним `error`
