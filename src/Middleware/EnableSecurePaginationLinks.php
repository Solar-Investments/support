<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnableSecurePaginationLinks
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (app()->isLocal()) {
            return $next($request);
        }

        // Indicate that the request came in through a secure channel
        $request->server->set('HTTPS', 'on');

        return $next($request);
    }
}
