<?php

declare(strict_types=1);

namespace SolarInvestments\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;

class AllowHealthCheckRequestsDuringMaintenance extends PreventRequestsDuringMaintenance
{
    protected $except = [
        '/probes/liveness',
        '/probes/liveness/backend',
        '/probes/liveness/scheduler',
        '/probes/liveness/worker',
        '/probes/readiness',
    ];
}
