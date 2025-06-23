<?php

declare(strict_types=1);

namespace SolarInvestments\Providers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use function is_array;

class RedisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        tap($this->app['config'], function (Repository $config): void {
            /** @var array<string, mixed> $redis */
            $redis = $config->get('database.redis', []);

            if (Arr::has($redis, 'clusters')) {
                return;
            }

            if (! $this->shouldEnableClusters()) {
                return;
            }

            $clusters = $this->extractClusters($redis);

            if ($clusters->isEmpty()) {
                return;
            }

            $config->set('database.redis', [
                'client' => Arr::get($redis, 'client'),
                'options' => Arr::get($redis, 'options'),
                'clusters' => $clusters->toArray(),
            ]);
        });
    }

    public function extractClusters(array $config): Collection
    {
        return collect($config)
            ->except('client', 'options')
            ->filter(fn (mixed $value): bool => is_array($value))
            ->filter(fn (array $array): bool => Arr::has($array, 'url') || Arr::has($array, [
                'host',
                'port',
            ]))
            ->map(fn (array $server): array => [$server]);
    }

    public function shouldEnableClusters(): bool
    {
        if (Env::get('FORCE_REDIS_CLUSTER', false)) {
            return true;
        }

        if (! $this->app->isProduction()) {
            return false;
        }

        if (Env::get('GCP_PROJECT_ID', 'helio-platform') !== 'helio-platform') {
            return false;
        }

        $hasProductionEnv = File::exists(base_path('.env.production.encrypted'));
        $hasHelioWorkflow = File::exists(base_path('.github/workflows/helio.yml'));

        return $hasProductionEnv && $hasHelioWorkflow;
    }
}
