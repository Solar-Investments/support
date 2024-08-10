<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Tests\TestCase;

class MacroServiceProviderTest extends TestCase
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function urlData(): array
    {
        return [
            ['http://localhost', 'localhost'],
            ['https://example.com', 'example.com'],
            ['https://example.com/foo', 'example.com'],
            ['https://example.com:8080', 'example.com'],
            ['https://foo.example.com', 'foo.example.com'],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function requestUrlData(): array
    {
        return [
            ['http://localhost', ''],
            ['http://localhost/', '/'],
            ['http://localhost/test', '/test'],
            ['http://localhost:8080/test', '/test'],
        ];
    }

    #[Test, DataProvider('urlData')]
    public function it_can_get_the_app_host(string $url, string $expected): void
    {
        config()->set('app.url', $url);

        $this->assertSame($expected, App::host());
    }

    #[Test, DataProvider('urlData')]
    public function it_can_get_the_host(string $url, string $expected): void
    {
        $this->assertSame($expected, Str::host($url));
    }

    #[Test, DataProvider('requestUrlData')]
    public function it_can_create_a_url_from_a_request(string $url, string $path): void
    {
        $this->assertSame($url, URL::fromRequest(
            request: Request::create($url),
            path: $path
        ));
    }
}
