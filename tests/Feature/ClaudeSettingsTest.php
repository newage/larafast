<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClaudeSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stored_value_overrides_env_default(): void
    {
        config(['claude.CLAUDE_MAX_TOKENS' => 1024]);
        Setting::create(['key' => 'CLAUDE_MAX_TOKENS', 'value' => '2048']);

        $this->assertSame('2048', Setting::resolve('CLAUDE_MAX_TOKENS'));
    }

    public function test_empty_value_falls_back_to_env_default(): void
    {
        config(['claude.CLAUDE_MAX_TOKENS' => 1024]);
        Setting::create(['key' => 'CLAUDE_MAX_TOKENS', 'value' => null]);

        $this->assertSame(1024, Setting::resolve('CLAUDE_MAX_TOKENS'));
        $this->assertSame(4, Setting::resolve('MAX_MESSAGES'));
    }

    public function test_api_key_is_encrypted_at_rest(): void
    {
        Setting::create(['key' => Setting::API_KEY, 'value' => 'sk-ant-secret-x7Qf']);

        $raw = DB::table('settings')->where('key', Setting::API_KEY)->value('value');

        $this->assertNotSame('sk-ant-secret-x7Qf', $raw);
        $this->assertSame('sk-ant-secret-x7Qf', Setting::resolve(Setting::API_KEY));
    }

    public function test_broken_ciphertext_falls_back_to_env_default(): void
    {
        config(['claude.ANTHROPIC_API_KEY' => 'sk-from-env']);
        DB::table('settings')->insert(['key' => Setting::API_KEY, 'value' => 'not-a-ciphertext']);

        $this->assertSame('sk-from-env', Setting::resolve(Setting::API_KEY));
    }
}
