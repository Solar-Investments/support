<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
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

    /**
     * @return array<int, array<int, bool|int|string>>
     */
    public static function environmentData(): array
    {
        return [
            [1, true],
            [0, false],
            ['1', true],
            ['0', false],
            [true, true],
            [false, false],
            ['true', true],
            ['false', false],
        ];
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    public static function statamicControlPanelData(): array
    {
        return [
            [null, null],
            ['cp', 'cp'],
            ['/cp', 'cp'],
            ['cp/', 'cp'],
            ['/cp/', 'cp'],
        ];
    }

    #[Test, DataProvider('urlData')]
    public function it_can_get_the_app_host(string $url, string $expected): void
    {
        config()->set('app.url', $url);

        $this->assertSame($expected, App::host());
    }

    #[Test, DataProvider('environmentData')]
    public function it_can_determine_if_the_app_is_running_in_ci(
        bool|int|string $value,
        bool $expected
    ): void {
        if (app()->runningCI()) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $_SERVER['CI'] = $value;

        $this->assertSame($expected, App::runningCI());
        $this->assertSame($expected, app()->runningCI());
    }

    #[Test, DataProvider('statamicControlPanelData')]
    public function it_can_get_the_statamic_control_panel_route(?string $route, ?string $expected): void
    {
        if ($route !== null) {
            config()->set('statamic.cp.route', $route);
        }

        $this->assertSame($expected, Route::statamicControlPanel());
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
