<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function in_array;
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

        abort_unless($this->isUsingVpn($request), Response::HTTP_FORBIDDEN);

        return $next($request);
    }

    public function isUsingVpn(Request $request): bool
    {
        if (($ips = $this->allowedIps()) === []) {
            return false;
        }

        if ($ips === ['*']) {
            return true;
        }

        if (($clientIp = $request->ip()) === null) {
            return false;
        }

        foreach ($ips as $ip) {
            if (Str::contains($ip, '/') && $this->ipWithinCidr($ip, $clientIp)) {
                return true;
            }
        }

        return in_array($clientIp, $ips, strict: true);
    }

    /**
     * @return array|array<int, string>
     */
    public function allowedIps(): array
    {
        /** @var array|array<int, string>|string $values */
        $values = config('vpn.ip_addresses') ?? [];

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
