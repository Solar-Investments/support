<?php

declare(strict_types=1);

namespace SolarInvestments;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->register(Providers\MacroServiceProvider::class);
        $this->app->register(Providers\MiddlewareServiceProvider::class);
        $this->app->register(Providers\UrlServiceProvider::class);
    }
}
