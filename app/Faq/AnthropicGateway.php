<?php

namespace App\Faq;

use Anthropic\Client;

class AnthropicGateway implements ClaudeGateway
{
    public function __construct(
        private readonly Client $client,
        private readonly string $model,
        private readonly ?string $effort,
    ) {}

    public function complete(string $system, string $user, int $maxTokens, bool $quick = false): string
    {
        $params = [
            'model' => $this->model,
            'maxTokens' => $maxTokens,
            'system' => $system,
            'messages' => [['role' => 'user', 'content' => $user]],
        ];
        if ($quick) {
            // Thinking tokens count against maxTokens; the short rewrite step must not spend them.
            $params['thinking'] = ['type' => 'disabled'];
            $params['outputConfig'] = ['effort' => 'low'];
        } elseif ($this->effort) {
            $params['outputConfig'] = ['effort' => $this->effort];
        }

        $message = $this->client->messages->create(...$params);

        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        return trim($text);
    }
}
