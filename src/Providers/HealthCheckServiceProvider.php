<?php

declare(strict_types=1);

namespace SolarInvestments\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use SolarInvestments\Console\Commands\HeartbeatCommand;
use SolarInvestments\Http\Controllers\ProbeController;
use SolarInvestments\Jobs\HeartbeatJob;

class HealthCheckServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->app->make(ProbeController::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            return;
        }

        $this->commands(HeartbeatCommand::class);

        $this->app->booted(function (): void {
            $schedule = $this->app->make(Schedule::class);
            $schedule->job(new HeartbeatJob())->everyMinute();
            $schedule->command('healthcheck:heartbeat')->everyMinute();
        });
    }
}
