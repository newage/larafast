<?php

namespace App\Faq;

final readonly class ChatMessage
{
    public function __construct(
        public string $question,
        public ?string $answer = null,
    ) {}
}
