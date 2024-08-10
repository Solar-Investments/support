<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HideFromRobotsOnOrigin
{
    /**
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     * @noinspection UnknownInspectionInspection
     */
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var Response $response */
        $response = $next($request);

        if (app()->host() !== $request->host()) {
            $response->headers->set('X-Robots-Tag', 'none');
        }

        return $response;
    }
}
