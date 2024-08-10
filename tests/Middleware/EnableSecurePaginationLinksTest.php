<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Middleware;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Middleware\EnableSecurePaginationLinks;
use SolarInvestments\Tests\TestCase;

class EnableSecurePaginationLinksTest extends TestCase
{
    #[Test]
    public function it_can_indicate_that_the_request_came_in_through_a_secure_channel(): void
    {
        $request = new Request();

        $middleware = new EnableSecurePaginationLinks();

        $middleware->handle($request, function (Request $request): void {
            $this->assertSame('on', $request->server->get('HTTPS'));
        });
    }

    #[Test]
    public function it_cannot_handle_requests_when_running_locally(): void
    {
        $this->app['env'] = 'local';

        $request = new Request();

        $middleware = new EnableSecurePaginationLinks();

        $middleware->handle($request, function (Request $request): void {
            $this->assertNull($request->server->get('HTTPS'));
        });
    }
}
