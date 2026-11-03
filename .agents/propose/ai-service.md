додай новий сервіс `ai-service` в докері — Python + FastAPI + LangChain з окремим API ендпоінтом POST /chat
Nginx як Веб-сервер / reverse proxy.
ANTHROPIC_API_KEY=
CLAUDE_MODEL=claude-haiku-5-5
# Effort: low | medium | high | xhigh | max (empty = API default for the model)
CLAUDE_EFFORT=low
# Limits: output tokens per Claude call, request size
CLAUDE_MAX_TOKENS=1024
MAX_MESSAGES=4
MAX_MESSAGE_CHARS=200
VECTOR_MIN_SCORE=0.84
у сервіса векторна БД Pinecone
PINECONE_API_KEY=
PINECONE_INDEX_NAME=faq
# Seed: index field_map must embed this field; namespace empty = default
PINECONE_TEXT_FIELD=text
PINECONE_NAMESPACE=
python скрипт для додавання даних в векторну БД Pinecone. Данні знаходяться в файлі `data/faq.json`
приклад seed скрипта на js: `.agents/propose/seed-pinecone.js`, `.agents/propose/seed-lib.js`, поля lang, project видалені, інша логіка така сама.
python скрипт для вимірювання VECTOR_MIN_SCORE векторної БД Pinecone, приклад seed скрипта на js: `.agents/propose/measure-score.js`
логіка: на /chat отримуємо до 4х повідомлень (MAX_MESSAGES), потрібно для контекста, і 1 запитання, може бути тільки запитання
```json
{
  "messages":[
    {
      "question":"What are his key achievements?",
      "answer":"....."
    },
    {
      "question":"Last question without answer?"
    }
  ]
}
```
тільки останнє повідомлення, без відповіді, шукаємо у векторній БД Pinecone. знайдену відповідь з Pinecone і запит, з попереднім контекстом якщо є, відправляємо в claude, для отримання відповіді.
системний промпт для claude: відповідати польською, нічого не вигадувати, данні брати тільки з контексту, іншого не відповідати.
відповідь повинна бути в форматі
```json
{
    "type": "answer",
    "message": "....."
}
```
