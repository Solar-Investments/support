<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Middleware\HideFromRobotsOnOrigin;
use SolarInvestments\Tests\TestCase;

class HideFromRobotsOnOriginTest extends TestCase
{
    #[Test]
    public function it_can_hide_from_robots_when_the_origin_is_different(): void
    {
        $request = new Request();

        $request->headers->set('Host', 'origin.localhost');

        $response = (new HideFromRobotsOnOrigin())
            ->handle($request, static fn (): Response => new Response());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('none', $response->headers->get('X-Robots-Tag'));
    }

    #[Test]
    public function it_cannot_hide_from_robots_when_the_origin_is_the_same(): void
    {
        $request = new Request();

        $request->headers->set('Host', 'localhost');

        $response = (new HideFromRobotsOnOrigin())
            ->handle($request, static fn (): Response => new Response());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNull($response->headers->get('X-Robots-Tag'));
    }
}
