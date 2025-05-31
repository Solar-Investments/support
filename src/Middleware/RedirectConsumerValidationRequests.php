<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function sprintf;

class RedirectConsumerValidationRequests
{
    /**
     * @param  Closure(Request): (RedirectResponse|Response)  $next
     * @return RedirectResponse|Response
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('consumer-validation/v2/*')) {
            return redirect()->away(sprintf(
                'https://www.fixr.com%s',
                $request->getRequestUri()
            ));
        }

        return $next($request);
    }
}
