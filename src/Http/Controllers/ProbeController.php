<?php

declare(strict_types=1);

namespace SolarInvestments\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use SolarInvestments\Models\Heartbeat;
use Throwable;

class ProbeController
{
    public function liveness(): ResponseFactory|Response
    {
        $services = [
            'backend' => fn (): Response => $this->livenessBackend(),
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
