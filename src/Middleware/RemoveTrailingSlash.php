<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class RemoveTrailingSlash
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        if (($path = $request->getPathInfo()) === '/') {
            return $next($request);
        }

        if (Str::endsWith($path, '/')) {
            return redirect()->to(
                path: URL::fromRequest(
                    request: $request,
                    path: rtrim($path, '/')
                ),
                status: Response::HTTP_MOVED_PERMANENTLY
            );
        }

        return $next($request);
    }
}
