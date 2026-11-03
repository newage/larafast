<?php

namespace Tests\Feature;

use App\Http\CloudflareProxies;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_ip', fn (Request $r) => $r->ip());
    }

    protected function tearDown(): void
    {
        @unlink(CloudflareProxies::path());
        CloudflareProxies::flush();

        parent::tearDown();
    }

    public function test_cloudflare_check_can_be_turned_off_for_local_development(): void
    {
        config(['cloudflare.enabled' => false]);

        $this->withServerVariables(['REMOTE_ADDR' => '172.70.1.1'])
            ->get('/_ip', ['X-Forwarded-For' => '203.0.113.7'])
            ->assertContent('172.70.1.1');
    }

    public function test_refreshed_list_replaces_the_builtin_one(): void
    {
        CloudflareProxies::store(['198.51.100.0/24']);

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.9'])
            ->get('/_ip', ['X-Forwarded-For' => '203.0.113.7'])
            ->assertContent('203.0.113.7');
        $this->withServerVariables(['REMOTE_ADDR' => '172.70.1.1'])
            ->get('/_ip', ['X-Forwarded-For' => '203.0.113.7'])
            ->assertContent('172.70.1.1');
    }

    public function test_refresh_command_stores_valid_ranges(): void
    {
        Http::fake([
            'www.cloudflare.com/ips-v4' => Http::response("198.51.100.0/24\n203.0.113.0/24\n"),
            'www.cloudflare.com/ips-v6' => Http::response("2001:db8::/32\n"),
        ]);

        $this->artisan('cloudflare:refresh-ips')->assertSuccessful();

        CloudflareProxies::flush();
        $this->assertSame(['198.51.100.0/24', '203.0.113.0/24', '2001:db8::/32'], CloudflareProxies::ranges());
    }

    public function test_refresh_failure_or_garbage_keeps_the_previous_list(): void
    {
        CloudflareProxies::store(['198.51.100.0/24']);

        Http::fake(['www.cloudflare.com/ips-v4' => Http::response('<html>blocked</html>'), 'www.cloudflare.com/ips-v6' => Http::response('', 500)]);
        $this->artisan('cloudflare:refresh-ips')->assertFailed();

        Http::fake(['www.cloudflare.com/ips-v4' => Http::response("198.51.100.0/99\n"), 'www.cloudflare.com/ips-v6' => Http::response('2001:db8::/32')]);
        $this->artisan('cloudflare:refresh-ips')->assertFailed();

        CloudflareProxies::flush();
        $this->assertSame(['198.51.100.0/24'], CloudflareProxies::ranges());
    }

    public function test_refresh_is_scheduled_every_ten_minutes(): void
    {
        $event = collect(app(Schedule::class)->events())->first(fn ($e) => str_contains($e->command, 'cloudflare:refresh-ips'));

        $this->assertNotNull($event);
        $this->assertSame('*/10 * * * *', $event->expression);
    }

    public function test_client_ip_comes_from_forwarded_for_when_peer_is_cloudflare(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '172.70.1.1'])
            ->get('/_ip', ['X-Forwarded-For' => '203.0.113.7'])
            ->assertContent('203.0.113.7');
    }

    public function test_spoofed_client_ip_is_ignored_for_the_real_client_behind_cloudflare(): void
    {
        // The client forged 1.1.1.1; Cloudflare appended the real address.
        $this->withServerVariables(['REMOTE_ADDR' => '172.70.1.1'])
            ->get('/_ip', ['X-Forwarded-For' => '1.1.1.1, 203.0.113.7'])
            ->assertContent('203.0.113.7');
    }

    public function test_forwarded_for_is_ignored_when_peer_is_not_cloudflare(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.9'])
            ->get('/_ip', ['X-Forwarded-For' => '203.0.113.7'])
            ->assertContent('198.51.100.9');
    }
}
