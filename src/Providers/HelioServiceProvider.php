<?php

declare(strict_types=1);

namespace SolarInvestments\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use SolarInvestments\Services\Helio;

class HelioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! File::isDirectory(base_path('.helio'))) {
            return;
        }

        if (! $this->app->configurationIsCached()) {
            Helio::configureLogging($this->app);
            Helio::configureStatamic($this->app);
        }
    }
}
