<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cloudflare may change its edge ranges; only relevant where the app is behind it (off locally).
Schedule::command('cloudflare:refresh-ips')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->when(fn () => config('cloudflare.enabled'));
