<?php

declare(strict_types=1);

namespace SolarInvestments\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use SolarInvestments\Middleware;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * @var array<int, class-string>
     */
    protected array $middleware = [
        Middleware\EnableSecurePaginationLinks::class,
        Middleware\HideFromRobotsOnOrigin::class,
        Middleware\LowerPathCasing::class,
        Middleware\RemoveTrailingSlash::class,
    ];

    /**
     * @var array<string, array<int, class-string>>
     */
    protected array $middlewareGroups = [
        'web' => [
            Middleware\RequireVpn::class,
        ],
    ];

    protected Kernel $kernel;

    /**
     * @throws BindingResolutionException
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->kernel = $this->app->make(Kernel::class);
    }

    public function boot(): void
    {
        $this->registerConfig();
        $this->configureMiddleware();
        $this->configureMiddlewareGroups();
    }

    protected function configureMiddleware(): void
    {
        foreach ($this->middleware as $middleware) {
            $this->kernel->pushMiddleware($middleware);
        }
    }

    protected function configureMiddlewareGroups(): void
    {
        foreach ($this->middlewareGroups as $group => $middlewares) {
            foreach ($middlewares as $middleware) {
                $this->kernel->appendMiddlewareToGroup($group, $middleware);
            }
        }
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../../config/vpn.php' => config_path('vpn.php'),
        ], 'vpn-config');
    }
}
