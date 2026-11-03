<?php

namespace Tests\Unit\Faq;

use App\Faq\ChatRequestValidator;
use App\Faq\InvalidChatRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ChatRequestValidatorTest extends TestCase
{
    private function validator(): ChatRequestValidator
    {
        return new ChatRequestValidator(maxMessages: 4, maxChars: 200);
    }

    public function test_single_question_is_accepted(): void
    {
        $messages = $this->validator()->validate([['question' => 'Ile kosztuje strona?']]);

        $this->assertCount(1, $messages);
        $this->assertNull($messages[0]->answer);
    }

    public function test_history_is_accepted(): void
    {
        $messages = $this->validator()->validate([
            ['question' => 'Czy robicie chatboty?', 'answer' => 'Tak.'],
            ['question' => 'A ile to kosztuje?'],
        ]);

        $this->assertSame('Tak.', $messages[0]->answer);
    }

    public function test_exactly_max_messages_and_chars_are_accepted(): void
    {
        $history = array_fill(0, 3, ['question' => str_repeat('ą', 200), 'answer' => str_repeat('ę', 200)]);

        $this->assertCount(4, $this->validator()->validate([...$history, ['question' => 'x']]));
    }

    public function test_answer_longer_than_max_chars_is_accepted(): void
    {
        $messages = $this->validator()->validate([
            ['question' => 'q', 'answer' => str_repeat('a', 500)],
            ['question' => 'q'],
        ]);

        $this->assertSame(500, mb_strlen($messages[0]->answer));
    }

    /** @return iterable<string, array{mixed}> */
    public static function invalidBodies(): iterable
    {
        yield 'not an array' => ['text'];
        yield 'empty' => [[]];
        yield 'too many' => [array_fill(0, 5, ['question' => 'q', 'answer' => 'a'])];
        yield 'long question' => [[['question' => str_repeat('a', 201)]]];
        yield 'blank question' => [[['question' => '  ']]];
        yield 'last with null answer' => [[['question' => 'q', 'answer' => null]]];
        yield 'last with answer' => [[['question' => 'q', 'answer' => 'a']]];
        yield 'earlier without answer' => [[['question' => 'q'], ['question' => 'q']]];
        yield 'question not a string' => [[['question' => ['x']]]];
    }

    #[DataProvider('invalidBodies')]
    public function test_invalid_bodies_are_rejected(mixed $body): void
    {
        $this->expectException(InvalidChatRequest::class);

        $this->validator()->validate($body);
    }
}
