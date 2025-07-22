<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function is_string;

class RequireVpn
{
    /**
     * @throws HttpException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (app()->isLocal()) {
            return $next($request);
        }

        if ($request->routeIs('probes.*') || $request->is('up')) {
            return $next($request);
        }

        abort_unless($this->isUsingVpn($request), Response::HTTP_FORBIDDEN);

        return $next($request);
    }

    public function isUsingVpn(Request $request): bool
    {
        if (($allowedIps = $this->allowedIps()) === []) {
            return false;
        }

        if ($allowedIps === ['*']) {
            return true;
        }

        foreach ($request->ips() as $clientIp) {
            foreach ($allowedIps as $allowedIp) {
                if (Str::contains($allowedIp, '/')) {
                    if ($this->ipWithinCidr($allowedIp, $clientIp)) {
                        return true;
                    }
                } elseif ($clientIp === $allowedIp) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array|array<int, string>
     */
    public function allowedIps(): array
    {
        /** @var array|array<int, string>|string|null $values */
        $values = config('vpn.ip_addresses');

        if ($values === null) {
            return ['*'];
        }

        if (is_string($values)) {
            $values = Str::of($values)
                ->explode(',')
                ->filter()
                ->toArray();
        }

        return $values;
    }

    public function ipWithinCidr(string $cidr, ?string $ip = null): bool
    {
        if ($ip === null) {
            return false;
        }

        [$network, $maskLength] = explode('/', $cidr);
        $maskLength = (int) $maskLength;

        $ipBinary = ip2long($ip);
        $networkBinary = ip2long($network);

        $mask = -1 << (32 - $maskLength);

        return ($ipBinary & $mask) === ($networkBinary & $mask);
    }
}
