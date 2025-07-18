<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Enums\HeartbeatType;
use SolarInvestments\Models\Heartbeat;
use SolarInvestments\Tests\TestCase;

class HeartbeatTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function scope_job(): void
    {
        $scope = Heartbeat::job();

        $job = Heartbeat::create(['type' => HeartbeatType::Job]);
        $schedule = Heartbeat::create(['type' => HeartbeatType::Schedule]);

        $result = $scope->get();

        $this->assertTrue($result->contains($job));
        $this->assertFalse($result->contains($schedule));
    }

    #[Test]
    public function scope_not_stale_includes_recent_heartbeats(): void
    {
        $scope = Heartbeat::notStale();

        $heartbeat = Heartbeat::create([
            'type' => HeartbeatType::Job,
        ]);

        $result = $scope->get();

        $this->assertTrue($result->contains($heartbeat));
    }

    #[Test]
    public function scope_not_stale_excludes_stale_heartbeats(): void
    {
        $scope = Heartbeat::notStale();

        $this->travelTo(now()->subMinutes(10));

        $heartbeat = Heartbeat::create([
            'type' => HeartbeatType::Job,
        ]);

        $this->travelBack();

        $result = $scope->get();

        $this->assertFalse($result->contains($heartbeat));
    }

    #[Test]
    public function scope_schedule(): void
    {
        $scope = Heartbeat::schedule();

        $job = Heartbeat::create(['type' => HeartbeatType::Job]);
        $schedule = Heartbeat::create(['type' => HeartbeatType::Schedule]);

        $result = $scope->get();

        $this->assertTrue($result->contains($schedule));
        $this->assertFalse($result->contains($job));
    }
}
