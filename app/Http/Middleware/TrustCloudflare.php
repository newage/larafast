<?php

namespace App\Http\Middleware;

use App\Http\CloudflareProxies;
use Illuminate\Http\Middleware\TrustProxies;

/** Trusts X-Forwarded-* only from Cloudflare edges; trusts nothing when `cloudflare.enabled` is off. */
class TrustCloudflare extends TrustProxies
{
    protected function proxies()
    {
        return config('cloudflare.enabled') ? CloudflareProxies::ranges() : null;
    }
}
