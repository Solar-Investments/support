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

    /**
     * @return array<int, array<int, string>>
     */
    public static function mixedCaseFilePathData(): array
    {
        return [
            ['http://localhost/FILE.js'],
            ['http://localhost/FILE.JS'],
            ['http://localhost:8080/FILE.css'],
            ['http://localhost:8080/FILE.CSS'],
            ['http://localhost:8080/FILE.jpeg'],
            ['http://localhost:8080/FILE.JPEG'],
            ['http://localhost/FILE.js?foo=bar'],
            ['http://localhost/FILE.JS?foo=bar'],
            ['http://localhost:8080/FILE.css?foo=bar'],
            ['http://localhost:8080/FILE.CSS?foo=bar'],
            ['http://localhost:8080/FILE.jpeg?foo=bar'],
            ['http://localhost:8080/FILE.JPEG?foo=bar'],
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

    #[Test, DataProvider('mixedCaseFilePathData')]
    public function it_cannot_redirect_requests_for_files_with_mixed_casing(string $url): void
    {
        $request = Request::create($url);

        $response = (new LowerPathCasing())
            ->handle($request, fn (): Response => new Response());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isRedirect());
    }
}
