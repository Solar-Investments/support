<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use function now;

class SetFastlySurrogateKey
{
    /**
     * @param  Closure(Request): (RedirectResponse|Response)  $next
     * @return RedirectResponse|Response
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $this->surrogateKeys()->each(function (array $surrogate) use ($request, $response): void {
            $ttl = now()->addMinutes(5);

            $surrogateKey = trim($surrogate['key']);

            /** @var Collection<int, non-falsy-string> $surrogatePaths */
            $surrogatePaths = Cache::remember(
                key: $this->cacheKeyForSurrogateKeyPaths($surrogateKey),
                ttl: $ttl,
                callback: static function () use ($surrogate): Collection {
                    /** @var Collection<int, string> $paths */
                    $paths = collect($surrogate['paths'])
                        ->map(static fn (string $path): string => Str::of($path)
                            ->trim()
                            ->ltrim('/')
                            ->lower()
                            ->toString()
                        )
                        ->filter()
                        ->unique();

                    return $paths;
                }
            );

            /** @var string $surrogateKeys */
            $surrogateKeys = Cache::remember(
                key: $this->cacheKeyForSurrogateKey($surrogateKey),
                ttl: $ttl,
                callback: function () use ($request, $surrogateKey, $surrogatePaths): string {
                    $keys = collect();

                    if (Str::startsWith(Str::lower($request->path()), $surrogatePaths)) {
                        $keys->push($surrogateKey);
                    }

                    return $keys->prepend(
                        $this->defaultSurrogateKey()
                    )->implode(' ');
                }
            );

            $response->header('Surrogate-Key', $surrogateKeys);
        });

        $response->setPublic();

        return $response;
    }

    /**
     * @return Collection<int, array{key: string, paths: array<int, string>}>
     */
    public function surrogateKeys(): Collection
    {
        return collect(config('fastly.surrogate_keys'));
    }

    public function defaultSurrogateKey(): string
    {
        return trim(config('fastly.default_surrogate_key'));
    }

    public function cacheKeyForSurrogateKey(string $key): string
    {
        return sprintf('fastly.surrogate-keys.%s', Str::slug($key));
    }

    public function cacheKeyForSurrogateKeyPaths(string $key): string
    {
        return sprintf('%s.paths', $this->cacheKeyForSurrogateKey($key));
    }
}
