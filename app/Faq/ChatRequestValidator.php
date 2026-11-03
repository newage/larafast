<?php

namespace App\Faq;

final class ChatRequestValidator
{
    public function __construct(
        private readonly int $maxMessages,
        private readonly int $maxChars,
    ) {}

    /**
     * @return list<ChatMessage>
     *
     * @throws InvalidChatRequest
     */
    public function validate(mixed $messages): array
    {
        if (! is_array($messages) || ! array_is_list($messages) || $messages === []) {
            throw new InvalidChatRequest('messages must be a non-empty list');
        }
        if (count($messages) > $this->maxMessages) {
            throw new InvalidChatRequest("messages must contain at most {$this->maxMessages} items");
        }

        $last = count($messages) - 1;
        $result = [];
        foreach ($messages as $i => $message) {
            $question = $this->text($message, 'question', $i);
            if ($i === $last) {
                if (is_array($message) && array_key_exists('answer', $message)) {
                    throw new InvalidChatRequest('the last message must not have an answer');
                }
                $result[] = new ChatMessage($question);

                continue;
            }
            if (! isset($message['answer'])) {
                throw new InvalidChatRequest("messages[$i] must have an answer");
            }
            $result[] = new ChatMessage($question, $this->text($message, 'answer', $i));
        }

        return $result;
    }

    private function text(mixed $message, string $field, int $i): string
    {
        $value = is_array($message) ? ($message[$field] ?? null) : null;
        $name = "messages[$i].$field";
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidChatRequest("$name must be a non-empty string");
        }
        if (mb_strlen($value) > $this->maxChars) {
            throw new InvalidChatRequest("$name must be at most {$this->maxChars} characters");
        }

        return $value;
    }
}
