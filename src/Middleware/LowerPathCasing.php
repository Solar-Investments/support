<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
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

        if ($this->pathIsForStatamicControlPanel($path)) {
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

    protected function pathIsForStatamicControlPanel(string $path): bool
    {
        if (($cp = $this->statamicControlPanelPath()) === null) {
            return false;
        }

        return Str::startsWith($path, $cp);
    }

    protected function statamicControlPanelPath(): ?string
    {
        $route = Route::statamicControlPanel();

        return $route !== null ? sprintf('/%s/', $route) : null;
    }
}
