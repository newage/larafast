<?php

namespace App\Http;

/**
 * Cloudflare edge ranges, the only peers allowed to set X-Forwarded-* (see TrustCloudflare).
 * The list is refreshed into a JSON file by `cloudflare:refresh-ips`; the built-in copy is the fallback
 * for a fresh install or an unreadable file. Source: https://www.cloudflare.com/ips-v4 and /ips-v6.
 */
final class CloudflareProxies
{
    private const DEFAULTS = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18',
        '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17',
        '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32',
        '2a06:98c0::/29', '2c0f:f248::/32',
    ];

    /** @var list<string>|null */
    private static ?array $memo = null;

    public static function path(): string
    {
        return storage_path('app/cloudflare-ips.json');
    }

    /** @return list<string> */
    public static function ranges(): array
    {
        return self::$memo ??= self::stored() ?? self::DEFAULTS;
    }

    /**
     * Atomically replace the stored list. Nothing is written unless every entry is a valid CIDR.
     *
     * @param  list<string>  $ranges
     */
    public static function store(array $ranges): void
    {
        $tmp = self::path().'.tmp';
        file_put_contents($tmp, json_encode($ranges, JSON_THROW_ON_ERROR));
        rename($tmp, self::path());
        self::$memo = null;
    }

    /** Drop the in-process copy so the next call re-reads the file. */
    public static function flush(): void
    {
        self::$memo = null;
    }

    public static function isValidCidr(string $cidr): bool
    {
        if (substr_count($cidr, '/') !== 1) {
            return false;
        }
        [$address, $prefix] = explode('/', $cidr);
        $packed = inet_pton($address);
        if ($packed === false || ! ctype_digit($prefix)) {
            return false;
        }

        return (int) $prefix <= strlen($packed) * 8;
    }

    /** @return list<string>|null */
    private static function stored(): ?array
    {
        $json = @file_get_contents(self::path());
        $ranges = $json === false ? null : json_decode($json, true);
        if (! is_array($ranges) || $ranges === [] || array_filter($ranges, fn ($r) => ! is_string($r) || ! self::isValidCidr($r))) {
            return null;
        }

        return array_values($ranges);
    }
}
