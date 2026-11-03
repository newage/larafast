<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    private const SECTIONS = ['services', 'projects', 'process', 'chat', 'contact'];

    public function test_home_page_is_polish_and_public(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="pl"', false)
            ->assertSee('<title>Strony www i automatyzacja AI</title>', false)
            ->assertSee('<meta name="description"', false)
            ->assertSee('Strony, które sprzedają.')
            ->assertSee('Automatyzacje, które pracują.');
    }

    public function test_home_page_has_all_sections_in_order(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (self::SECTIONS as $id) {
            $response->assertSee('id="'.$id.'"', false);
        }

        $response->assertSeeInOrder([
            'Przykładowy proces',
            'Dobre',
            'Dwie rzeczy, zrobione porządnie.',
            'Platformy, przy których pracowałem.',
            'Trzy kroki od pomysłu do przekazania.',
            'Zapytaj mojego asystenta.',
            'Opisz, co spowalnia Twoją firmę.',
            'Polityka prywatności',
        ]);
    }

    public function test_home_page_card_counts(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertSame(4, substr_count($content, 'data-card="service"'));
        $this->assertSame(2, substr_count($content, 'data-card="project"'));
        $this->assertSame(3, substr_count($content, 'data-card="step"'));
    }

    public function test_navigation_links_to_section_anchors(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (['services', 'projects', 'process', 'chat'] as $id) {
            $response->assertSee('href="#'.$id.'"', false);
        }
    }

    public function test_calls_to_action_lead_to_chat_without_mailto(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        preg_match_all('/<a\b[^>]*>\s*Czat z (?:AI|asystentem)\s*<\/a>/u', $content, $ctas);
        $this->assertCount(3, $ctas[0]);

        foreach ($ctas[0] as $link) {
            $this->assertStringContainsString('href="#chat"', $link);
            $this->assertStringNotContainsString('mailto:', $link);
        }

        $this->assertStringNotContainsString('Umów', $content);
        $this->assertStringContainsString('href="mailto:hello@vadymleontiev.dev"', $content);
    }

    public function test_page_links_to_privacy_policy_and_warns_about_ai(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(2, substr_count($content, 'href="/polityka-prywatnosci"'));
        $this->assertStringContainsString('Rozmawiasz z asystentem AI. Nie podawaj danych wrażliwych.', $content);
        $this->assertStringContainsString('Opisz, co spowalnia Twoją firmę.', $content);
    }

    public function test_hero_shows_the_website_process(): void
    {
        $this->get('/')->assertOk()
            ->assertSeeInOrder([
                'Przykładowy proces', 'Jak pracuję',
                'Ustalam z klientem, czego potrzebuje', 'Rozmowa',
                'Przygotowuję projekt graficzny i logo, klient je zatwierdza', 'Akceptacja',
                'Tworzę stronę na podstawie zatwierdzonego projektu', 'Kod',
                'Publikuję stronę na hostingu', 'Online',
            ])
            ->assertDontSee('Zapytanie klienta')
            ->assertDontSee('Czeka na Ciebie')
            ->assertDontSee('Gotowe');
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

    public function test_page_has_technologies_chat_chips_and_footer(): void
    {
        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Laravel', 'PHP', 'Python', 'FastAPI', 'LangChain', 'Claude API', 'MCP', 'Docker'])
            ->assertSee('Ile kosztuje strona?')
            ->assertSee('Co można zautomatyzować?')
            ->assertSee('Czy to bezpieczne dla danych klientów?')
            ->assertSee('Polityka prywatności')
            ->assertDontSee('Regulamin');
    }

    public function test_chat_widget_works_on_the_client_only(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-chat-form', $content);
        $this->assertStringContainsString('Wyślij', $content);
        $this->assertStringNotContainsString('action=', $content);
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
