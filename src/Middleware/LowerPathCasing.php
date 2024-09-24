<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LowerPathCasing
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        if (($path = $request->getPathInfo()) === '/') {
            return $next($request);
        }

        if (Str::of($path)->isMatch($this->filePattern())) {
            return $next($request);
        }

        if (Str::of($path)->isMatch('/[A-Z]/')) {
            return redirect()->to(
                path: URL::fromRequest(
                    request: $request,
                    path: Str::lower($path)
                ),
                status: Response::HTTP_MOVED_PERMANENTLY
            );
        }

        return $next($request);
    }

    protected function filePattern(): string
    {
        return '/\.[a-z0-9]{2,4}(\?.*)?$/i';
    }
}
