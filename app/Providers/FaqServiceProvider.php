<?php

namespace App\Providers;

use Anthropic\Client;
use App\Faq\AnthropicGateway;
use App\Faq\ChatPipeline;
use App\Faq\ChatRequestValidator;
use App\Faq\ClaudeChat;
use App\Faq\ClaudeGateway;
use App\Faq\FaqRetriever;
use App\Faq\PineconeIndex;
use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class FaqServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PineconeIndex::class, fn () => PineconeIndex::fromConfig());

        $this->app->bind(ClaudeGateway::class, fn () => new AnthropicGateway(
            new Client(apiKey: (string) Setting::resolve(Setting::API_KEY)),
            (string) Setting::resolve('CLAUDE_MODEL'),
            Setting::resolve('CLAUDE_EFFORT') ?: null,
        ));

        $this->app->bind(ChatPipeline::class, fn ($app) => new ChatPipeline(
            new ChatRequestValidator(
                (int) Setting::resolve('MAX_MESSAGES'),
                (int) Setting::resolve('MAX_MESSAGE_CHARS'),
            ),
            new ClaudeChat($app->make(ClaudeGateway::class), (int) Setting::resolve('CLAUDE_MAX_TOKENS')),
            $app->make(FaqRetriever::class),
            (float) Setting::resolve('VECTOR_MIN_SCORE'),
        ));
    }

    public function boot(): void
    {
        // The endpoint spends Claude tokens and is public: 10 requests per minute per IP.
        RateLimiter::for('chat', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
    }
}
