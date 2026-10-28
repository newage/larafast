<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Claude Defaults
    |--------------------------------------------------------------------------
    |
    | Defaults for the Claude chat parameters. A non-empty value saved on the
    | admin Setup page overrides each of these; read them through
    | App\Models\Setting::value() rather than directly.
    |
    */

    'ANTHROPIC_API_KEY' => env('ANTHROPIC_API_KEY'),
    'CLAUDE_MODEL' => env('CLAUDE_MODEL', 'claude-haiku-5-5'),
    // low | medium | high | xhigh | max (empty = API default for the model)
    'CLAUDE_EFFORT' => env('CLAUDE_EFFORT', 'low'),
    'CLAUDE_MAX_TOKENS' => env('CLAUDE_MAX_TOKENS', 1024),
    'MAX_MESSAGES' => env('MAX_MESSAGES', 4),
    'MAX_MESSAGE_CHARS' => env('MAX_MESSAGE_CHARS', 200),
    'VECTOR_MIN_SCORE' => env('VECTOR_MIN_SCORE', 0.84),

];
