<?php

use App\Http\Middleware\TrustCloudflare;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::group([], base_path('routes/chat.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Cloudflare the client IP comes from X-Forwarded-For only when the peer is a Cloudflare edge.
        $middleware->replace(TrustProxies::class, TrustCloudflare::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(fn (TooManyRequestsHttpException $e, Request $request) => $request->is('chat')
            ? response()->json(['type' => 'error', 'message' => 'Too many requests'], 429, $e->getHeaders())
            : null);
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
