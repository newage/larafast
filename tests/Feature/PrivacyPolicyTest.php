<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_privacy_policy_page_is_polish_and_public(): void
    {
        $this->get('/polityka-prywatnosci')
            ->assertOk()
            ->assertSee('<html lang="pl"', false)
            ->assertSee('<title>Polityka prywatności</title>', false)
            ->assertSee('Vadym Leontiev')
            ->assertSee('hello@vadymleontiev.dev')
            ->assertSee('netcup')
            ->assertSee('Google')
            ->assertSee('30 dni');
    }

    public function test_privacy_policy_page_links_back_to_home(): void
    {
        $content = $this->get('/polityka-prywatnosci')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<a\b[^>]*\shref="\/"[^>]*>\s*Strona główna\s*<\/a>/u', $content);
        $this->assertMatchesRegularExpression('/<a\b[^>]*\shref="\/"[^>]*>\s*Wróć do strony głównej\s*<\/a>/u', $content);
    }

    public function test_privacy_policy_page_has_no_placeholders(): void
    {
        $text = strip_tags($this->get('/polityka-prywatnosci')->assertOk()->getContent());

        $this->assertDoesNotMatchRegularExpression('/\[[A-ZŁ ]+[^\]]*\]/u', $text);
    }
}
