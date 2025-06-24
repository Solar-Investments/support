<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Middleware;

use Illuminate\Http\Request;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Middleware\RequireVpn;
use SolarInvestments\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequireVpnTest extends TestCase
{
    #[Test]
    public function it_can_allow_vpn_ips(): void
    {
        config()->set('vpn.ip_addresses', ['192.168.1.1']);

        $request = new Request();

        $request->server->set('REMOTE_ADDR', '192.168.1.1');

        $middleware = new RequireVpn();

        $middleware->handle($request, fn () => $this->assertTrue(true));
    }

    #[Test]
    public function it_can_allow_all_ips_when_not_configured(): void
    {
        $request = new Request();

        $middleware = new RequireVpn();

        $middleware->handle($request, fn () => $this->assertTrue(true));
    }

    #[Test]
    public function it_can_throw_an_exception_when_a_vpn_is_not_used(): void
    {
        config()->set('vpn.ip_addresses', ['192.168.1.1']);

        $this->expectException(HttpException::class);

        $request = new Request();

        $middleware = new RequireVpn();

        $middleware->handle($request, fn () => $this->fail());
    }

    #[Test]
    #[DefineEnvironment('local')]
    public function it_cannot_handle_requests_when_running_locally(): void
    {
        $request = new Request();

        $middleware = new RequireVpn();

        $middleware->handle($request, fn () => $this->assertTrue(true));
    }

    #[Test]
    public function it_can_determine_if_a_vpn_is_being_used(): void
    {
        $request = new Request();

        $request->server->set('REMOTE_ADDR', '192.168.1.1');

        $middleware = new RequireVpn();

        config()->set('vpn.ip_addresses', []);
        $this->assertFalse($middleware->isUsingVpn($request));

        config()->set('vpn.ip_addresses', ['*']);
        $this->assertTrue($middleware->isUsingVpn($request));

        config()->set('vpn.ip_addresses', ['192.168.1.1']);
        $this->assertTrue($middleware->isUsingVpn($request));

        config()->set('vpn.ip_addresses', ['192.168.0.0/16']);
        $this->assertTrue($middleware->isUsingVpn($request));
    }

    #[Test]
    public function it_can_get_the_allowed_ips(): void
    {
        $middleware = new RequireVpn();

        config()->set('vpn.ip_addresses');
        $this->assertSame(['*'], $middleware->allowedIps());

        config()->set('vpn.ip_addresses', '');
        $this->assertSame([], $middleware->allowedIps());

        config()->set('vpn.ip_addresses', []);
        $this->assertSame([], $middleware->allowedIps());

        config()->set('vpn.ip_addresses', '*');
        $this->assertSame(['*'], $middleware->allowedIps());

        config()->set('vpn.ip_addresses', ['*']);
        $this->assertSame(['*'], $middleware->allowedIps());

        config()->set('vpn.ip_addresses', '192.168.1.1');
        $this->assertSame(['192.168.1.1'], $middleware->allowedIps());

        config()->set('vpn.ip_addresses', ['192.168.1.1']);
        $this->assertSame(['192.168.1.1'], $middleware->allowedIps());

        config()->set('vpn.ip_addresses', '192.168.0.0/16');
        $this->assertSame(['192.168.0.0/16'], $middleware->allowedIps());

        config()->set('vpn.ip_addresses', ['192.168.0.0/16']);
        $this->assertSame(['192.168.0.0/16'], $middleware->allowedIps());
    }

    #[Test]
    public function it_can_determine_if_an_ip_is_within_a_cidr(): void
    {
        $ip = '192.168.1.1';
        $network = '192.168.1.0';

        $middleware = new RequireVpn();

        $this->assertFalse($middleware->ipWithinCidr("$network/8"));

        foreach ([8, 12, 16, 20, 24, 28, 30] as $maskLength) {
            $this->assertTrue($middleware->ipWithinCidr("$network/$maskLength", $ip));
        }

        $this->assertFalse($middleware->ipWithinCidr("$network/32", $ip));
    }

    #[Test]
    public function it_can_allow_when_any_forwarded_ip_matches_cidr(): void
    {
        $request = new Request();

        $request->server->set('REMOTE_ADDR', '203.0.113.1');
        $request->headers->set('X-Forwarded-For', '203.0.113.1, 10.1.2.3');

        Request::setTrustedProxies(
            proxies: ['203.0.113.1'],
            trustedHeaderSet: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT
        );

        $middleware = new RequireVpn();

        config()->set('vpn.ip_addresses', ['10.0.0.0/8']);
        $this->assertTrue($middleware->isUsingVpn($request));
    }

    #[Test]
    public function it_cannot_allow_when_no_forwarded_ips_match(): void
    {
        $request = new Request();

        $request->server->set('REMOTE_ADDR', '198.51.100.1');
        $request->headers->set('X-Forwarded-For', '198.51.100.1, 203.0.113.2');

        $middleware = new RequireVpn();

        config()->set('vpn.ip_addresses', ['10.0.0.0/8']);
        $this->assertFalse($middleware->isUsingVpn($request));
    }
}
