<?php

declare(strict_types=1);

namespace SolarInvestments\Providers;

use Illuminate\Support\ServiceProvider;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * @var array<int, string>
     */
    protected array $configs = [
        'fastly',
        'vpn',
    ];

    public function boot(): void
    {
        $this->registerConfig();
    }

    protected function registerConfig(): void
    {
        foreach ($this->configs as $config) {
            $this->publishes([
                __DIR__."/../../config/$config.php" => config_path("$config.php"),
            ], "$config-config");
        }
    }
}
