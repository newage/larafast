<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use Tests\TestCase;

class ContactTest extends TestCase
{
    private const SUBJECT = 'Mam proces, który wymaga automatyzacji.';

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.contact_email' => 'hello@vadymleontiev.dev']);
        RateLimiter::clear('contact');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Anna',
            'email' => 'anna@example.com',
            'message' => 'Ręcznie przepisujemy faktury.',
        ], $overrides);
    }

    public function test_it_sends_the_message_to_the_owner(): void
    {
        Mail::fake();

        $this->postJson('/contact', $this->payload())
            ->assertOk()
            ->assertJson(['type' => 'ok']);

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail): bool {
            return $mail->hasTo('hello@vadymleontiev.dev')
                && $mail->hasReplyTo('anna@example.com')
                && $mail->hasSubject(self::SUBJECT)
                && str_contains($mail->render(), 'Ręcznie przepisujemy faktury.');
        });
    }

    public function test_name_and_message_are_optional(): void
    {
        Mail::fake();

        $this->postJson('/contact', ['email' => 'anna@example.com'])->assertOk();

        Mail::assertSent(ContactMessage::class);
    }

    public function test_it_rejects_a_missing_or_invalid_email(): void
    {
        Mail::fake();

        $this->postJson('/contact', $this->payload(['email' => '']))->assertStatus(422)->assertJsonValidationErrors('email');
        $this->postJson('/contact', $this->payload(['email' => 'anna@']))->assertStatus(422)->assertJsonValidationErrors('email');

        Mail::assertNothingSent();
    }

    public function test_it_rejects_too_long_fields(): void
    {
        Mail::fake();

        $this->postJson('/contact', $this->payload(['message' => str_repeat('a', 3001)]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('message');
        $this->postJson('/contact', $this->payload(['name' => str_repeat('a', 121)]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        Mail::assertNothingSent();
    }

    public function test_it_accepts_a_message_of_exactly_the_limit(): void
    {
        Mail::fake();

        $this->postJson('/contact', $this->payload(['message' => str_repeat('a', 3000)]))->assertOk();

        Mail::assertSent(ContactMessage::class);
    }

    public function test_a_filled_honeypot_succeeds_without_sending(): void
    {
        Mail::fake();

        $this->postJson('/contact', $this->payload(['company_website' => 'http://spam.test']))
            ->assertOk()
            ->assertJson(['type' => 'ok']);

        Mail::assertNothingSent();
    }

    public function test_it_is_throttled_with_a_json_429(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/contact', $this->payload())->assertOk();
        }

        $this->postJson('/contact', $this->payload())
            ->assertStatus(429)
            ->assertJson(['type' => 'error']);
    }

    public function test_a_throttled_request_is_json_even_without_an_accept_header(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $this->payload())->assertOk();
        }

        $this->post('/contact', $this->payload())
            ->assertStatus(429)
            ->assertJson(['type' => 'error']);
    }

    public function test_a_mail_failure_returns_a_502_json_error(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('smtp down'));

        $this->postJson('/contact', $this->payload())
            ->assertStatus(502)
            ->assertJson(['type' => 'error']);
    }
}
