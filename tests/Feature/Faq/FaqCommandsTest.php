<?php

namespace Tests\Feature\Faq;

use Tests\TestCase;

class FaqCommandsTest extends TestCase
{
    public function test_seed_dry_run_without_credentials_only_counts(): void
    {
        config(['services.pinecone.api_key' => null, 'services.pinecone.index_name' => null]);

        $this->artisan('faq:seed', ['--dry-run' => true])
            ->expectsOutputToContain('130 records')
            ->assertSuccessful();
    }

    public function test_seed_without_credentials_names_the_missing_variable(): void
    {
        config(['services.pinecone.api_key' => null, 'services.pinecone.index_name' => 'idx']);

        $this->artisan('faq:seed')
            ->expectsOutputToContain('PINECONE_API_KEY is not set')
            ->assertFailed();
    }
}
