<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trust Cloudflare proxies
    |--------------------------------------------------------------------------
    |
    | When on, X-Forwarded-* is honoured only from Cloudflare edge addresses
    | (kept fresh by `cloudflare:refresh-ips`). Off by default in the local
    | environment, where there is no Cloudflare in front of nginx.
    |
    */

    'enabled' => (bool) env('TRUST_CLOUDFLARE', env('APP_ENV', 'production') !== 'local'),

];
