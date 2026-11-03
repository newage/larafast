<?php

namespace Tests\Feature\Faq;

use App\Faq\ClaudeGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<array{system: string, user: string, quick: bool}> */
    private array $claudeCalls = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.pinecone' => ['api_key' => 'k', 'index_name' => 'idx', 'host' => 'idx.test', 'text_field' => 'text', 'namespace' => null],
            'claude.VECTOR_MIN_SCORE' => 0.84,
        ]);
        RateLimiter::clear('chat');
    }

    private function fakeClaude(string $reply = 'Odpowiedź.', bool $failAnswer = false, bool $failRewrite = false): void
    {
        $this->app->instance(ClaudeGateway::class, new class($this, $reply, $failAnswer, $failRewrite) implements ClaudeGateway
        {
            public function __construct(private ChatTest $test, private string $reply, private bool $failAnswer, private bool $failRewrite) {}

            public function complete(string $system, string $user, int $maxTokens, bool $quick = false): string
            {
                $this->test->recordClaude($system, $user, $quick);
                if ($quick ? $this->failRewrite : $this->failAnswer) {
                    throw new RuntimeException('claude down');
                }

                return $quick ? 'Ile kosztuje chatbot?' : $this->reply;
            }
        });
    }

    public function recordClaude(string $system, string $user, bool $quick): void
    {
        $this->claudeCalls[] = compact('system', 'user', 'quick');
    }

    private function fakePinecone(array $hits): void
    {
        Http::fake(['idx.test/*' => Http::response(['matches' => $hits])]);
    }

    private function hit(string $id, string $parent, float $score, string $answer = 'Od 2000 zł.'): array
    {
        return ['_id' => $id, '_score' => $score, 'parent_id' => $parent, 'answer' => $answer];
    }

    public function test_answers_from_the_found_context(): void
    {
        $this->fakeClaude('Strona kosztuje od 2000 zł.');
        $this->fakePinecone([$this->hit('koszt-01', 'koszt', 0.95), $this->hit('koszt-03', 'koszt', 0.9)]);

        $this->postJson('/chat', ['messages' => [['question' => 'Ile kosztuje strona internetowa?']]])
            ->assertOk()
            ->assertExactJson(['type' => 'answer', 'message' => 'Strona kosztuje od 2000 zł.']);

        $this->assertCount(1, $this->claudeCalls, 'no history: no rewrite call');
        $this->assertSame(1, substr_count($this->claudeCalls[0]['user'], 'Od 2000 zł.'), 'record is in the context once');
    }

    public function test_two_records_go_to_the_context_and_at_most_three(): void
    {
        $this->fakeClaude();
        $this->fakePinecone([
            $this->hit('a-01', 'a', 0.99, 'A1'), $this->hit('b-01', 'b', 0.95, 'B1'),
            $this->hit('c-01', 'c', 0.9, 'C1'), $this->hit('d-01', 'd', 0.88, 'D1'), $this->hit('e-01', 'e', 0.5, 'E1'),
        ]);

        $this->postJson('/chat', ['messages' => [['question' => 'q']]])->assertOk();

        $user = $this->claudeCalls[0]['user'];
        $this->assertStringContainsString('A1', $user);
        $this->assertStringContainsString('C1', $user);
        $this->assertStringNotContainsString('D1', $user);
        $this->assertStringNotContainsString('E1', $user);
    }

    public function test_nothing_above_threshold_returns_the_fixed_phrase_without_claude(): void
    {
        $this->fakeClaude();
        $this->fakePinecone([$this->hit('koszt-01', 'koszt', 0.5)]);

        $this->postJson('/chat', ['messages' => [['question' => 'Jaka jest stolica Francji?']]])
            ->assertOk()
            ->assertJsonPath('type', 'answer')
            ->assertJsonPath('message', fn (string $m) => str_starts_with($m, 'Przepraszam'));

        $this->assertSame([], $this->claudeCalls);
    }

    public function test_follow_up_is_rewritten_and_searched_by_the_rewritten_question(): void
    {
        $this->fakeClaude();
        $this->fakePinecone([$this->hit('chatbot-01', 'chatbot', 0.9)]);

        $this->postJson('/chat', ['messages' => [
            ['question' => 'Czy robicie chatboty?', 'answer' => 'Tak.'],
            ['question' => 'A ile to kosztuje?'],
        ]])->assertOk();

        $this->assertTrue($this->claudeCalls[0]['quick']);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/search') && $r['score_by'][0]['query'] === 'Ile kosztuje chatbot?');
        $this->assertStringContainsString('A ile to kosztuje?', $this->claudeCalls[1]['user'], 'answer step gets the original question');
    }

    public function test_rewrite_failure_falls_back_to_the_raw_question(): void
    {
        $this->fakeClaude(failRewrite: true);
        $this->fakePinecone([$this->hit('chatbot-01', 'chatbot', 0.9)]);

        $this->postJson('/chat', ['messages' => [
            ['question' => 'Czy robicie chatboty?', 'answer' => 'Tak.'],
            ['question' => 'A ile to kosztuje?'],
        ]])->assertOk();

        Http::assertSent(fn ($r) => str_contains($r->url(), '/search') && $r['score_by'][0]['query'] === 'A ile to kosztuje?');
    }

    public function test_invalid_body_is_422_in_the_error_format(): void
    {
        $this->postJson('/chat', ['messages' => []])
            ->assertStatus(422)
            ->assertJsonPath('type', 'error');

        $this->postJson('/chat', ['messages' => [array_fill_keys(['question', 'answer'], 'x')]])->assertStatus(422);
        $this->postJson('/chat', [])->assertStatus(422)->assertJsonPath('type', 'error');
    }

    public function test_too_many_and_too_long_are_422(): void
    {
        $this->postJson('/chat', ['messages' => array_fill(0, 5, ['question' => 'q', 'answer' => 'a'])])->assertStatus(422);
        $this->postJson('/chat', ['messages' => [['question' => str_repeat('a', 201)]]])->assertStatus(422);
    }

    public function test_pinecone_failure_is_502(): void
    {
        $this->fakeClaude();
        Http::fake(['idx.test/*' => Http::response('boom', 500)]);

        $this->postJson('/chat', ['messages' => [['question' => 'q']]])
            ->assertStatus(502)
            ->assertJsonPath('type', 'error');
    }

    public function test_claude_answer_failure_is_502(): void
    {
        $this->fakeClaude(failAnswer: true);
        $this->fakePinecone([$this->hit('koszt-01', 'koszt', 0.95)]);

        $this->postJson('/chat', ['messages' => [['question' => 'q']]])
            ->assertStatus(502)
            ->assertJsonPath('type', 'error');
    }

    public function test_requests_over_the_limit_get_429_before_reaching_the_pipeline(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/chat', ['messages' => []])->assertStatus(422);
        }

        $this->postJson('/chat', ['messages' => []])->assertStatus(429);
    }
}
