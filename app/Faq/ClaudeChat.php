<?php

namespace App\Faq;

use Illuminate\Support\Facades\Log;
use Throwable;

class ClaudeChat
{
    public const REWRITE_MAX_TOKENS = 128;

    public const NOT_FOUND_MESSAGE = 'Przepraszam, nie mam informacji na ten temat. '
        .'Proszę zapytać o moje usługi lub skontaktować się ze mną bezpośrednio.';

    private const REWRITE_PROMPT = 'Na podstawie historii rozmowy zamień ostatnie pytanie użytkownika w samodzielne pytanie po polsku, '
        .'zrozumiałe bez historii. Zachowaj sens pytania, nic nie dodawaj i nie odpowiadaj na nie. '
        .'Zwróć wyłącznie treść pytania.';

    private const ANSWER_PROMPT = 'Jesteś asystentem na stronie internetowej. Odpowiadasz wyłącznie po polsku. '
        .'Używaj wyłącznie informacji z podanego kontekstu, historii rozmowy i pytania. '
        .'Niczego nie wymyślaj. Jeśli pytanie wykracza poza kontekst, nie odpowiadaj na nie '
        .'i uprzejmie poinformuj, że nie masz takiej informacji. Odpowiadaj zwięźle i uprzejmie.';

    public function __construct(
        private readonly ClaudeGateway $claude,
        private readonly int $answerMaxTokens,
    ) {}

    /**
     * Make the last question self-contained. Without history, or on failure, it is returned as is.
     *
     * @param  list<ChatMessage>  $history
     */
    public function rewriteQuestion(array $history, string $question): string
    {
        if ($history === []) {
            return $question;
        }

        $prompt = "Historia rozmowy:\n".$this->historyText($history)."\n\nOstatnie pytanie: $question";
        try {
            $rewritten = $this->claude->complete(self::REWRITE_PROMPT, $prompt, self::REWRITE_MAX_TOKENS, quick: true);
        } catch (Throwable $e) {
            Log::warning('question rewrite failed, using the raw question', ['exception' => $e]);

            return $question;
        }

        return $rewritten !== '' ? $rewritten : $question;
    }

    /**
     * @param  list<array{answer: string}>  $records
     * @param  list<ChatMessage>  $history
     */
    public function answer(array $records, array $history, string $question): string
    {
        $context = implode("\n\n", array_map(
            fn (array $r, int $i) => '['.($i + 1).'] '.$r['answer'],
            $records,
            array_keys($records),
        ));
        $parts = ["Kontekst:\n$context"];
        if ($history !== []) {
            $parts[] = "Historia rozmowy:\n".$this->historyText($history);
        }
        $parts[] = "Pytanie: $question";

        return $this->claude->complete(self::ANSWER_PROMPT, implode("\n\n", $parts), $this->answerMaxTokens);
    }

    /** @param  list<ChatMessage>  $history */
    private function historyText(array $history): string
    {
        return implode("\n", array_map(
            fn (ChatMessage $m) => "Użytkownik: {$m->question}\nAsystent: {$m->answer}",
            $history,
        ));
    }
}
