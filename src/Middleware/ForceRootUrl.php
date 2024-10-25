<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ForceRootUrl
{
    public function handle(Request $request, Closure $next): mixed
    {
        $url = config('app.url');

        if ($request->host() !== app()->host()) {
            $url = $request->getSchemeAndHttpHost();
        }

        $root = Str::of(
            string: $url
        )->rtrim(
            characters: '/'
        )->replace(
            search: 'http:',
            replace: 'https:',
            caseSensitive: false
        )->toString();

        URL::forceRootUrl($root);

        return $next($request);
    }
}
