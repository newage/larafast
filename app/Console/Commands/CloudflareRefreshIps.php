<?php

namespace App\Console\Commands;

use App\Http\CloudflareProxies;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class CloudflareRefreshIps extends Command
{
    protected $signature = 'cloudflare:refresh-ips';

    protected $description = 'Download the current Cloudflare IP ranges used by trusted proxies';

    public function handle(): int
    {
        try {
            $ranges = [...$this->fetch('https://www.cloudflare.com/ips-v4'), ...$this->fetch('https://www.cloudflare.com/ips-v6')];
        } catch (Throwable $e) {
            // The stored (or built-in) list stays in force.
            $this->error('Cloudflare IP refresh failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $before = CloudflareProxies::ranges();
        CloudflareProxies::store($ranges);
        $this->info(sprintf('%d ranges stored%s', count($ranges), $before === $ranges ? ' (unchanged)' : ''));

        return self::SUCCESS;
    }

    /** @return non-empty-list<string> */
    private function fetch(string $url): array
    {
        $lines = preg_split('/\s+/', trim(Http::timeout(15)->get($url)->throw()->body()), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            if (! CloudflareProxies::isValidCidr($line)) {
                throw new \UnexpectedValueException("$url returned an invalid range: ".mb_substr($line, 0, 60));
            }
        }
        if ($lines === []) {
            throw new \UnexpectedValueException("$url returned no ranges");
        }

        return $lines;
    }
}
