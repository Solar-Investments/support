<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionException;
use SolarInvestments\Middleware\ForceRootUrl;
use SolarInvestments\Tests\TestCase;

class ForceRootUrlTest extends TestCase
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function urlData(): array
    {
        return [
            ['example.com', 'https://example.com'],
            ['example.com:8000', 'https://example.com'],
            ['origin.example.com', 'https://origin.example.com'],
        ];
    }

    #[Test, DataProvider('urlData')]
    public function it_can_force_the_root_url(string $host, string $expected): void
    {
        config()->set('app.url', 'https://example.com');

        $request = new Request();

        $request->headers->set('Host', $host);

        (new ForceRootUrl())
            ->handle($request, static fn (): Response => new Response());

        /** @var UrlGenerator $url */
        $url = URL::getFacadeRoot();

        try {
            $forcedRoot = (new ReflectionClass($url))->getProperty('forcedRoot');
        } catch (ReflectionException) {
            $this->fail();
        }

        $this->assertSame($expected, $forcedRoot->getValue($url));
    }
}
