<?php

declare(strict_types=1);

namespace SolarInvestments\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use SolarInvestments\ServiceProvider;

/**
 * @property Application $app
 */
abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    /**
     * @param  Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        tap($app['config'], static function (Repository $config): void {
            $config->set('app.maintenance.driver', 'file');
            $config->set('cache.default', 'array');
            $config->set('database.default', 'testing');
            $config->set('mail.default', 'array');
            $config->set('queue.default', 'sync');
            $config->set('session.driver', 'array');
        });
    }

    protected function local(Application $app): void
    {
        $app['env'] = 'local';
    }

    protected function production(Application $app): void
    {
        $app['env'] = 'production';
    }
}
