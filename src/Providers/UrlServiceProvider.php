<?php

declare(strict_types=1);

namespace SolarInvestments\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class UrlServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->isLocal()) {
            return;
        }

        URL::forceScheme('https');
    }
}
