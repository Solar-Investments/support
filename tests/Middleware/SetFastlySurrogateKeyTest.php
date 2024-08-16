<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Middleware\SetFastlySurrogateKey;
use SolarInvestments\Tests\TestCase;

class SetFastlySurrogateKeyTest extends TestCase
{
    protected const DEFAULT_KEY = '<DEFAULT>';

    public static function surrogateKeys(): array
    {
        return [
            [
                [
                    'key' => '<KEY1>',
                    'paths' => [
                        'foo',
                    ],
                ],
            ],
            [
                [
                    'key' => '<KEY1>',
                    'paths' => [
                        'foo',
                        'bar',
                    ],
                ],
            ],
            [
                [
                    'key' => '<KEY1> <KEY2>',
                    'paths' => [
                        'foo',
                    ],
                ],
            ],
            [
                [
                    'key' => '<KEY1> <KEY2>',
                    'paths' => [
                        'foo',
                        'bar',
                    ],
                ],
            ],
            [
                [
                    'key' => '<KEY1>',
                    'paths' => [
                        '/foo',
                        '/bar',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function surrogateCacheKeys(): array
    {
        return [
            ['<KEY>', 'key'],
            ['<KEY1> <KEY2>', 'key1-key2'],
        ];
    }

    #[Test, DataProvider('surrogateKeys')]
    public function it_can_set_the_surrogate_key(array $surrogateKey): void
    {
        config()->set('fastly.surrogate_keys', [$surrogateKey]);

        $middleware = new SetFastlySurrogateKey();

        foreach ($surrogateKey['paths'] as $path) {
            $expected = sprintf(
                '%s %s',
                static::DEFAULT_KEY,
                $surrogateKey['key']
            );

            $request = Request::create($path);

            $response = $middleware
                ->handle($request, fn (Request $request): Response => new Response());

            $this->assertInstanceOf(Response::class, $response);
            $this->assertSame($expected, $response->headers->get('Surrogate-Key'));
        }
    }

    #[Test, DataProvider('surrogateKeys')]
    public function it_can_set_the_default_surrogate_key(array $surrogateKey): void
    {
        config()->set('fastly.surrogate_keys', [$surrogateKey]);

        $response = (new SetFastlySurrogateKey())
            ->handle(new Request(), fn (Request $request): Response => new Response());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(static::DEFAULT_KEY, $response->headers->get('Surrogate-Key'));
    }

    #[Test]
    public function it_can_mark_the_response_public(): void
    {
        $response = (new SetFastlySurrogateKey())
            ->handle(new Request(), fn (Request $request): Response => new Response());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->headers->hasCacheControlDirective('public'));
    }

    #[Test, DataProvider('surrogateKeys')]
    public function it_can_get_the_surrogate_keys(array $surrogateKey): void
    {
        config()->set('fastly.surrogate_keys', [$surrogateKey]);

        $middleware = new SetFastlySurrogateKey();

        $this->assertTrue($middleware->surrogateKeys()->contains($surrogateKey));
    }

    #[Test]
    public function it_can_get_the_default_surrogate_key(): void
    {
        $middleware = new SetFastlySurrogateKey();

        $this->assertSame(static::DEFAULT_KEY, $middleware->defaultSurrogateKey());
    }

    #[Test, DataProvider('surrogateCacheKeys')]
    public function it_can_get_the_cache_key_for_surrogate_key(string $key, string $expected): void
    {
        $expected = "fastly.surrogate-keys.$expected";

        $middleware = new SetFastlySurrogateKey();

        $this->assertSame($expected, $middleware->cacheKeyForSurrogateKey($key));
    }

    #[Test, DataProvider('surrogateCacheKeys')]
    public function it_can_get_the_cache_key_for_surrogate_key_paths(string $key, string $expected): void
    {
        $expected = "fastly.surrogate-keys.$expected.paths";

        $middleware = new SetFastlySurrogateKey();

        $this->assertSame($expected, $middleware->cacheKeyForSurrogateKeyPaths($key));
    }

    #[Test]
    public function it_can_get_the_cached_surrogate_keys(): void
    {
        config()->set('fastly.surrogate_keys', [
            [
                'key' => '<KEY>',
                'paths' => [
                    'foo',
                ],
            ],
        ]);

        $expected = sprintf(
            '%s %s',
            static::DEFAULT_KEY,
            '<KEY>'
        );

        $middleware = new SetFastlySurrogateKey();

        $request = Request::create('foo');

        $middleware->handle($request, fn (Request $request): Response => new Response());

        $this->assertIsString($value = Cache::get(
            key: $middleware->cacheKeyForSurrogateKey('<KEY>')
        ));

        $this->assertSame($expected, $value);
    }

    #[Test]
    public function it_can_get_the_cached_surrogate_key_paths(): void
    {
        config()->set('fastly.surrogate_keys', [
            [
                'key' => '<KEY>',
                'paths' => [
                    'foo',
                ],
            ],
        ]);

        $expected = collect(['foo']);

        $middleware = new SetFastlySurrogateKey();

        $request = Request::create('foo');

        $middleware->handle($request, fn (Request $request): Response => new Response());

        $this->assertInstanceOf(Collection::class, $value = Cache::get(
            key: $middleware->cacheKeyForSurrogateKeyPaths('<KEY>')
        ));

        $this->assertSame($expected->toArray(), $value->toArray());
    }

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('fastly.default_surrogate_key', static::DEFAULT_KEY);
    }
}
