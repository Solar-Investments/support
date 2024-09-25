<?php

declare(strict_types=1);

namespace SolarInvestments\Providers;

use GuzzleHttp\Psr7\Uri;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use function in_array;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * @noinspection StaticClosureCanBeUsedInspection
     * @noinspection UnknownInspectionInspection
     */
    public function register(): void
    {
        /**
         * Get the hostname from the application URL.
         */
        App::macro('host', fn (): string => Str::host(
            url: config('app.url')
        ));

        /**
         * Determine if the application is running in a CI environment.
         */
        App::macro('runningCI', fn (): bool => (bool) Env::get(
            key: 'CI',
            default: false
        ));

        /**
         * Get the Statamic control panel route.
         */
        Route::macro('statamicControlPanel', function (): ?string {
            /** @var string|null $value */
            $value = config('statamic.cp.route');

            return $value !== null ? Str::trim($value, '/') : null;
        });

        /**
         * Get the hostname from the given URL.
         */
        Str::macro('host', fn (string $url): string => parse_url(
            url: $url,
            component: PHP_URL_HOST
        ));

        /**
         * Create a URL from a request.
         */
        URL::macro('fromRequest', function (Request $request, string $path = ''): string {
            $uri = (new Uri())->withScheme(
                scheme: $request->getScheme()
            )->withHost(
                host: $request->getHost()
            )->withPath(
                path: $request->getBaseUrl().$path
            );

            if (! in_array($port = $request->getPort(), [80, 443], true)) {
                $uri = $uri->withPort($port);
            }

            if (($query = $request->getQueryString()) !== null) {
                $uri = $uri->withQuery($query);
            }

            return (string) $uri;
        });
    }
}
