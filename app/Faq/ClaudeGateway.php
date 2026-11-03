<?php

namespace App\Faq;

/** One text-in / text-out call to Claude. A seam so the chat logic is testable without the network. */
interface ClaudeGateway
{
    /**
     * @param  bool  $quick  no thinking, lowest effort: for short mechanical steps
     *
     * @throws \Throwable on any API failure
     */
    public function complete(string $system, string $user, int $maxTokens, bool $quick = false): string;
}
