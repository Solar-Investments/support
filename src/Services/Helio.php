<?php

declare(strict_types=1);

namespace SolarInvestments\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;

class Helio
{
    public static function bootstrapperBootstrapping(Application $app, string $bootstrapper): void
    {
        //
    }

    public static function bootstrapperBootstrapped(Application $app, string $bootstrapper): void
    {
        (match ($bootstrapper) {
            LoadConfiguration::class => static function () use ($app): void {
                //
            },
            HandleExceptions::class => static function () use ($app): void {
                //
            },
            default => static fn () => true,
        })();
    }
}
