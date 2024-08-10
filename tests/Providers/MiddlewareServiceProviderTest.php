<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Http\Kernel;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Middleware;
use SolarInvestments\Tests\TestCase;

class MiddlewareServiceProviderTest extends TestCase
{
    #[Test]
    public function it_can_add_middleware(): void
    {
        try {
            /** @var Kernel $kernel */
            $kernel = $this->app->make(Kernel::class);
        } catch (BindingResolutionException) {
            $this->fail();
        }

        $middleware = $kernel->getGlobalMiddleware();

        $this->assertContains(Middleware\EnableSecurePaginationLinks::class, $middleware);
        $this->assertContains(Middleware\HideFromRobotsOnOrigin::class, $middleware);
        $this->assertContains(Middleware\LowerPathCasing::class, $middleware);
        $this->assertContains(Middleware\RemoveTrailingSlash::class, $middleware);
    }

    #[Test]
    public function it_can_add_middleware_to_the_web_group(): void
    {
        try {
            /** @var Kernel $kernel */
            $kernel = $this->app->make(Kernel::class);
        } catch (BindingResolutionException) {
            $this->fail();
        }

        $middlewareGroups = $kernel->getMiddlewareGroups();

        $this->assertContains(Middleware\RequireVpn::class, $middlewareGroups['web']);
    }
}
