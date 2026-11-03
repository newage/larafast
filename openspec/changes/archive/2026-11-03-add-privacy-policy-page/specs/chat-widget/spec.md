# Spec Delta

## ADDED Requirements

### Requirement: Повідомлення про обробку даних
Під чатом SHALL показуватися текст «Rozmawiasz z asystentem AI. Nie podawaj danych wrażliwych.» з посиланням «Polityka prywatności» на `/polityka-prywatnosci`. Це повідомлення SHALL NOT впливати на відправку питань, історію чи показ помилок.

#### Scenario: Посилання на політику під чатом
- **WHEN** відвідувач відкриває головну
- **THEN** під віджетом чату є попередження і посилання «Polityka prywatności» з `href="/polityka-prywatnosci"`
