<?php

namespace Tests\Feature;

use App\Filament\Pages\Setup;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SetupPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // User does not implement FilamentUser, so Filament only admits users in the local environment.
        config(['app.env' => 'local']);
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin/setup')->assertRedirect('/admin/login');
    }

    public function test_admin_sees_polish_setup_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/setup')
            ->assertOk()
            ->assertSee('Klucz API Anthropic')
            ->assertSee('Zapisz');
    }

    public function test_values_are_saved_and_override_defaults(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Setup::class)
            ->fillForm(['CLAUDE_MAX_TOKENS' => '2048', 'CLAUDE_EFFORT' => 'high', 'VECTOR_MIN_SCORE' => '0.9'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('2048', Setting::resolve('CLAUDE_MAX_TOKENS'));
        $this->assertSame('high', Setting::resolve('CLAUDE_EFFORT'));
    }

    public function test_clearing_a_value_falls_back_to_env_default(): void
    {
        $this->actingAs(User::factory()->create());
        Setting::create(['key' => 'CLAUDE_MAX_TOKENS', 'value' => '2048']);

        Livewire::test(Setup::class)
            ->fillForm(['CLAUDE_MAX_TOKENS' => ''])
            ->call('save');

        $this->assertSame(config('claude.CLAUDE_MAX_TOKENS'), Setting::resolve('CLAUDE_MAX_TOKENS'));
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Setup::class)
            ->fillForm(['VECTOR_MIN_SCORE' => '1.5', 'CLAUDE_MAX_TOKENS' => '0', 'CLAUDE_EFFORT' => 'insane'])
            ->call('save')
            ->assertHasFormErrors(['VECTOR_MIN_SCORE', 'CLAUDE_MAX_TOKENS', 'CLAUDE_EFFORT']);

        $this->assertSame(0, Setting::count());
    }

    public function test_api_key_is_encrypted_masked_and_kept_when_field_is_empty(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Setup::class)
            ->fillForm([Setting::API_KEY => 'sk-ant-api03-secretx7Qf'])
            ->call('save');

        $raw = DB::table('settings')->where('key', Setting::API_KEY)->value('value');
        $this->assertNotSame('sk-ant-api03-secretx7Qf', $raw);

        $this->get('/admin/setup')
            ->assertSee('sk-ant-…x7Qf')
            ->assertDontSee('sk-ant-api03-secretx7Qf');

        Livewire::test(Setup::class)
            ->fillForm([Setting::API_KEY => '', 'CLAUDE_MODEL' => 'claude-opus-5-5'])
            ->call('save');

        $this->assertSame('sk-ant-api03-secretx7Qf', Setting::resolve(Setting::API_KEY));
    }
}
