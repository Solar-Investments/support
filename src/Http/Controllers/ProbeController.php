<?php

declare(strict_types=1);

namespace SolarInvestments\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use SolarInvestments\Models\Heartbeat;
use Throwable;

class ProbeController
{
    public function liveness(): ResponseFactory|Response
    {
        $services = [
            'backend' => fn (): Response => $this->livenessBackend(),
            'cache' => fn (): Response => $this->livenessCache(),
            'database' => fn (): Response => $this->livenessDatabase(),
        ];

        $errors = collect();

        foreach ($services as $name => $check) {
            $response = $check();

            if ($response->isServerError()) {
                $errors->push($name);
            }
        }

        if ($errors->isEmpty()) {
            return response('Application is live');
        }

        return response(
            'Liveness failures: '.$errors->implode(', '),
            Response::HTTP_SERVICE_UNAVAILABLE
        );
    }

    public function livenessBackend(): ResponseFactory|Response
    {
        return response('Backend service is running');
    }

    public function livenessCache(): ResponseFactory|Response
    {
        try {
            Cache::put(
                key: $key = '{probe}:liveness:cache',
                value: $value = 'OK',
                ttl: now()->addMinutes(5)
            );

            $actual = Cache::get($key);

            Cache::forget($key);

            throw_unless($value === $actual);
        } catch (Throwable) {
            return response(
                'Cache connection failed',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        return response('Cache connection successful');
    }

    public function livenessDatabase(): ResponseFactory|Response
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            return response(
                'Database connection failed',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        return response('Database connection successful');
    }

    public function livenessScheduler(): ResponseFactory|Response
    {
        try {
            Heartbeat::schedule()->notStale()->firstOrFail();
        } catch (Throwable) {
            return response(
                'Scheduler heartbeat missing or stale',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        return response('Scheduler is active');
    }

    public function livenessWorker(): ResponseFactory|Response
    {
        try {
            Heartbeat::job()->notStale()->firstOrFail();
        } catch (Throwable) {
            return response(
                'Queue heartbeat missing or stale',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        return response('Queue is active');
    }

    public function readiness(): ResponseFactory|Response
    {
        return response('Application is ready to receive traffic');
    }
}
