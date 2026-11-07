<?php

namespace Tests\Feature\Faq;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FaqCommandsTest extends TestCase
{
    public function test_seed_dry_run_without_credentials_only_counts(): void
    {
        config(['services.pinecone.api_key' => null, 'services.pinecone.index_name' => null]);

        $this->artisan('faq:seed', ['--dry-run' => true])
            ->expectsOutputToContain('133 records')
            ->assertSuccessful();
    }

    public function test_seed_without_credentials_names_the_missing_variable(): void
    {
        config(['services.pinecone.api_key' => null, 'services.pinecone.index_name' => 'idx']);

        $this->artisan('faq:seed')
            ->expectsOutputToContain('PINECONE_API_KEY is not set')
            ->assertFailed();
    }

    public function test_seed_reports_a_missing_file_without_a_stack_trace(): void
    {
        $this->artisan('faq:seed', ['file' => '/nonexistent/faq.json'])->assertFailed();
    }

    public function test_seed_reports_a_pinecone_failure(): void
    {
        config([
            'services.pinecone.api_key' => 'key',
            'services.pinecone.index_name' => 'idx',
            'services.pinecone.host' => 'https://idx.example.test',
        ]);
        Http::fake(['*' => Http::response(['error' => 'boom'], 500)]);

        $this->artisan('faq:seed')
            ->expectsOutputToContain('Pinecone request failed')
            ->assertFailed();
    }
}
