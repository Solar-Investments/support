<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Middleware\RemoveTrailingSlash;
use SolarInvestments\Tests\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RemoveTrailingSlashTest extends TestCase
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function redirectRequestUrlData(): array
    {
        return [
            ['http://localhost/test/', 'http://localhost/test'],
            ['http://localhost:8080/test/', 'http://localhost:8080/test'],
            ['http://localhost/test/?foo=bar', 'http://localhost/test?foo=bar'],
            ['http://localhost:8080/test/?foo=bar', 'http://localhost:8080/test?foo=bar'],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function nonRedirectRequestUrlData(): array
    {
        return [
            ['http://localhost'],
            ['http://localhost/test'],
            ['http://localhost:8080/test'],
            ['http://localhost/test?foo=bar'],
            ['http://localhost:8080/test?foo=bar'],
        ];
    }

    #[Test, DataProvider('redirectRequestUrlData')]
    public function it_can_redirect_requests_with_a_trailing_slash(string $url, string $expected): void
    {
        $request = Request::create($url);

        $response = (new RemoveTrailingSlash())
            ->handle($request, fn () => new Response());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertSame($expected, $response->headers->get('Location'));
    }

    #[Test, DataProvider('nonRedirectRequestUrlData')]
    public function it_cannot_redirect_requests_without_a_trailing_slash(string $url): void
    {
        $request = Request::create($url);

        $response = (new RemoveTrailingSlash())
            ->handle($request, fn (): Response => new Response());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isRedirect());
    }
}
