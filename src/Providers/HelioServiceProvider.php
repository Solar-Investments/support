<?php

declare(strict_types=1);

namespace SolarInvestments\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SolarInvestments\Services\Helio;

class HelioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! File::isDirectory(base_path('.helio'))) {
            return;
        }

        $this->app['events']->listen(
            'bootstrapping: *',
            fn (string $bootstrapper) => Helio::bootstrapperBootstrapping(
                $this->app,
                bootstrapper: Str::after($bootstrapper, 'bootstrapping: ')
            )
        );

        $this->app['events']->listen(
            'bootstrapped: *',
            fn (string $bootstrapper) => Helio::bootstrapperBootstrapped(
                $this->app,
                Str::after($bootstrapper, 'bootstrapped: ')
            )
        );
    }
}
