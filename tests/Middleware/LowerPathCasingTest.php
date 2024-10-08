<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Middleware\LowerPathCasing;
use SolarInvestments\Tests\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LowerPathCasingTest extends TestCase
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function mixedCasePathData(): array
    {
        return [
            ['http://localhost/TEST', 'http://localhost/test'],
            ['http://localhost:8080/TEST', 'http://localhost:8080/test'],
            ['http://localhost/TEST?foo=bar', 'http://localhost/test?foo=bar'],
            ['http://localhost:8080/TEST?foo=bar', 'http://localhost:8080/test?foo=bar'],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function lowerCasePathData(): array
    {
        return [
            ['http://localhost'],
            ['http://localhost/test'],
            ['http://localhost:8080/test'],
            ['http://localhost/test?foo=bar'],
            ['http://localhost:8080/test?foo=bar'],
        ];
    }

    public static function statamicControlPanelData(): array
    {
        return [
            ['http://localhost/cp/'],
            ['http://localhost/cp/dashboard'],
            ['http://localhost/cp/dashboard?foo=bar'],
            ['http://localhost/cp/dashboard?foo=bar&baz=qux'],
        ];
    }

    #[Test, DataProvider('mixedCasePathData')]
    public function it_can_redirect_requests_with_mixed_casing(string $url, string $expected): void
    {
        $request = Request::create($url);

        $response = (new LowerPathCasing())
            ->handle($request, fn () => new Response());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertSame($expected, $response->headers->get('Location'));
    }

    #[Test, DataProvider('lowerCasePathData')]
    public function it_cannot_redirect_requests_with_lower_casing(string $url): void
    {
        $request = Request::create($url);

        $response = (new LowerPathCasing())
            ->handle($request, fn (): Response => new Response());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isRedirect());
    }

    #[Test, DataProvider('statamicControlPanelData')]
    public function it_can_skip_statamic_control_panel_paths(string $url): void
    {
        config()->set('statamic.cp.route', 'cp');

        $request = Request::create($url);

        $response = (new LowerPathCasing())
            ->handle($request, fn (): Response => new Response());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isRedirect());
    }
}
