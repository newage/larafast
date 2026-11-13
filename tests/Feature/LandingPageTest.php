<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    private const SECTIONS = ['jak', 'uslugi', 'realizacje', 'rekomendacje', 'czat', 'kontakt'];

    public function test_home_page_is_polish_and_public(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="pl"', false)
            ->assertSee('<title>Vadym Leontiev · Automatyzacje AI, chatboty i aplikacje dla firm</title>', false)
            ->assertSee('<meta name="description"', false)
            ->assertSee('Chatboty i automatyzacje AI,')
            ->assertSee('które pracują za Twój zespół.');
    }

    public function test_home_page_has_all_sections_in_order(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (self::SECTIONS as $id) {
            $response->assertSee('id="'.$id.'"', false);
        }

        $response->assertSeeInOrder([
            'Chatboty i automatyzacje AI,',
            'Jak pracuję · dzień 1',
            'Jedna osoba zamiast software house',
            'Nie kolejna strona na WordPressie.',
            'Powtarzalną robotę oddaj automatom',
            'Proste zasady współpracy.',
            'Systemy, które zaprojektowałem i zbudowałem.',
            'Co mówią ludzie, z którymi pracowałem.',
            'Zamiast FAQ: zapytaj mojego asystenta AI.',
            'Masz proces, który zjada czas Twojego zespołu?',
            'Polityka prywatności',
        ]);
    }

    public function test_home_page_card_counts(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertSame(6, substr_count($content, 'class="svc'));
        $this->assertSame(3, substr_count($content, 'class="proj"'));
        $this->assertSame(3, substr_count($content, 'class="rec"'));
        $this->assertSame(4, substr_count($content, 'class="rule"'));
    }

    public function test_navigation_links_to_section_anchors(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (['jak', 'uslugi', 'realizacje', 'rekomendacje', 'czat'] as $id) {
            $response->assertSee('href="#'.$id.'"', false);
        }
    }

    public function test_call_buttons_open_the_calendar_in_a_new_tab_without_mailto(): void
    {
        $content = $this->get('/')->assertOk()->getContent();
        $calendar = config('site.calendar_url');

        preg_match_all('/<a\b[^>]*>\s*(?:<svg.*?<\/svg>)?\s*Umów 15 minut(?: rozmowy)?\s*<\/a>/su', $content, $buttons);
        $this->assertCount(4, $buttons[0]);

        foreach ($buttons[0] as $link) {
            $this->assertStringContainsString('href="'.$calendar.'"', $link);
            $this->assertStringContainsString('target="_blank"', $link);
            $this->assertStringContainsString('rel="noopener"', $link);
            $this->assertStringNotContainsString('mailto:', $link);
        }
    }

    public function test_calendar_url_comes_from_config(): void
    {
        config(['site.calendar_url' => 'https://calendar.example/book']);

        $this->get('/')->assertOk()->assertSee('href="https://calendar.example/book"', false);
    }

    public function test_contact_section_has_the_form_and_copyable_email(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('data-contact-form', false)
            ->assertSee('name="company_website"', false)
            ->assertSee('data-copy-email="hello@vadymleontiev.dev"', false)
            ->assertSee('kliknij, aby skopiować')
            ->assertSee('Wyślij');
    }

    public function test_page_links_to_privacy_policy_and_warns_about_ai(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(3, substr_count($content, 'href="/polityka-prywatnosci"'));
        $this->assertStringContainsString('Rozmawiasz z asystentem AI. Nie podawaj danych wrażliwych.', $content);
        $this->assertStringContainsString('href="mailto:hello@vadymleontiev.dev"', $content);
    }

    public function test_page_has_no_internal_links_except_anchors(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        preg_match_all('/<a\b[^>]*\shref="([^"]*)"/', $content, $matches);

        foreach ($matches[1] as $href) {
            $this->assertMatchesRegularExpression(
                '/^(#.*|mailto:.*|https?:\/\/.*|\/polityka-prywatnosci)$/',
                $href,
                "Unexpected internal link: {$href}"
            );
        }
    }

    public function test_project_cards_link_to_external_sites(): void
    {
        $this->get('/')
            ->assertSee('href="https://www.englishdom.com/"', false)
            ->assertSee('href="https://allrightpro.com/pl/"', false);
    }

    public function test_page_has_chat_chips_and_footer(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Ile kosztuje strona?')
            ->assertSee('Co można zautomatyzować?')
            ->assertSee('Czy to bezpieczne dla danych klientów?')
            ->assertSee('© '.now()->year.' Vadym Leontiev')
            ->assertSee('Polityka prywatności')
            ->assertDontSee('Regulamin');
    }

    public function test_chat_widget_works_on_the_client_only(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-chat-form', $content);
        $this->assertStringNotContainsString('<form data-chat-form action=', $content);
    }

    public function test_chat_widget_receives_limits_from_settings(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('data-max-messages="4"', false)
            ->assertSee('data-max-chars="'.config('claude.MAX_MESSAGE_CHARS').'"', false);

        Setting::create(['key' => 'MAX_MESSAGES', 'value' => '6']);

        $this->get('/')->assertOk()->assertSee('data-max-messages="6"', false);
    }
}
