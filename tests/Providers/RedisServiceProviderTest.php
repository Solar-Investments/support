<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Providers;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Providers\RedisServiceProvider;
use SolarInvestments\Tests\TestCase;

class RedisServiceProviderTest extends TestCase
{
    #[Test]
    public function it_can_extract_clusters(): void
    {
        $clusters = (new RedisServiceProvider($this->app))->extractClusters(
            config: config('database.redis')
        );

        $this->assertArrayHasKey('default', $clusters);
        $this->assertArrayHasKey('cache', $clusters);

        $this->assertSame([
            [
                'url' => null,
                'host' => '127.0.0.1',
                'username' => null,
                'password' => null,
                'port' => '6379',
                'database' => '0',
            ],
        ], $clusters->get('default'));
    }

    #[Test]
    #[DefineEnvironment('production')]
    #[DefineEnvironment('usesDefaultProjectId')]
    public function it_can_enable_clusters_when_all_conditions_are_met(): void
    {
        File::shouldReceive('exists')
            ->with(base_path('.env.production.encrypted'))
            ->andReturnTrue();

        File::shouldReceive('exists')
            ->with(base_path('.github/workflows/helio.yml'))
            ->andReturnTrue();

        $provider = new RedisServiceProvider($this->app);

        $this->assertTrue($provider->shouldEnableClusters());
    }

    #[Test]
    #[DefineEnvironment('local')]
    #[DefineEnvironment('usesDefaultProjectId')]
    public function it_can_disable_clusters_if_environment_is_not_production(): void
    {
        $provider = new RedisServiceProvider($this->app);

        $this->assertFalse($provider->shouldEnableClusters());
    }

    #[Test]
    #[DefineEnvironment('production')]
    #[DefineEnvironment('usesNonDefaultProjectId')]
    public function it_can_disable_clusters_if_not_default_project(): void
    {
        $provider = new RedisServiceProvider($this->app);

        $this->assertFalse($provider->shouldEnableClusters());
    }

    #[Test]
    #[DefineEnvironment('local')]
    #[DefineEnvironment('forcesRedisCluster')]
    public function it_can_allow_manual_override_with_force_flag(): void
    {
        $provider = new RedisServiceProvider($this->app);

        $this->assertTrue($provider->shouldEnableClusters());
    }
}
