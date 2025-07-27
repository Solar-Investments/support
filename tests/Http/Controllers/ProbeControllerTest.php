<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Enums\HeartbeatType;
use SolarInvestments\Http\Controllers\ProbeController;
use SolarInvestments\Models\Heartbeat;
use SolarInvestments\Tests\TestCase;

class ProbeControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function liveness_success(): void
    {
        $response = $this->get(route('probes.liveness'));

        $response->assertOk();
        $response->assertSeeText('Application is live');
    }

    #[Test]
    public function readiness_success(): void
    {
        $response = $this->get(route('probes.readiness'));

        $response->assertOk();
        $response->assertSeeText('Application is ready to receive traffic');
    }

    #[Test]
    public function liveness_backend_failure(): void
    {
        $this->app->bind(ProbeController::class, function () {
            return new class() extends ProbeController
            {
                public function livenessBackend(): Response
                {
                    return response('Backend failure', Response::HTTP_SERVICE_UNAVAILABLE);
                }
            };
        });

        $response = $this->get(route('probes.liveness'));

        $response->assertServerError();
        $response->assertSeeText('Liveness failures: backend');
    }

    #[Test]
    public function liveness_scheduler_success(): void
    {
        Heartbeat::create([
            'type' => HeartbeatType::Schedule,
        ]);

        $response = $this->get(route('probes.liveness.scheduler'));

        $response->assertOk();
        $response->assertSeeText('Scheduler is active');
    }

    #[Test]
    public function liveness_scheduler_failure(): void
    {
        $response = $this->get(route('probes.liveness.scheduler'));

        $response->assertServerError();
        $response->assertSeeText('Scheduler heartbeat missing or stale');
    }

    #[Test]
    public function liveness_worker_success(): void
    {
        Heartbeat::create([
            'type' => HeartbeatType::Job,
        ]);

        $response = $this->get(route('probes.liveness.worker'));

        $response->assertOk();
        $response->assertSeeText('Queue is active');
    }

    #[Test]
    public function liveness_worker_failure(): void
    {
        $response = $this->get(route('probes.liveness.worker'));

        $response->assertServerError();
        $response->assertSeeText('Queue heartbeat missing or stale');
    }
}
