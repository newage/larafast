<?php

namespace App\Faq;

use Throwable;

class ChatPipeline
{
    public function __construct(
        private readonly ChatRequestValidator $validator,
        private readonly ClaudeChat $chat,
        private readonly FaqRetriever $retriever,
        private readonly float $minScore,
    ) {}

    /**
     * @throws InvalidChatRequest
     * @throws UpstreamFailure
     */
    public function run(mixed $messages): string
    {
        $history = $this->validator->validate($messages);
        $last = array_pop($history);

        $searchQuery = $this->chat->rewriteQuestion($history, $last->question);

        try {
            $records = $this->retriever->search($searchQuery, $this->minScore);
        } catch (Throwable $e) {
            throw new UpstreamFailure('search failed', previous: $e);
        }
        if ($records === []) {
            return ClaudeChat::NOT_FOUND_MESSAGE;
        }

        try {
            return $this->chat->answer($records, $history, $last->question);
        } catch (Throwable $e) {
            throw new UpstreamFailure('answer generation failed', previous: $e);
        }
    }
}
