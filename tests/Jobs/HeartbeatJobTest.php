<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Jobs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Enums\HeartbeatType;
use SolarInvestments\Jobs\HeartbeatJob;
use SolarInvestments\Models\Heartbeat;
use SolarInvestments\Tests\TestCase;

class HeartbeatJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_job_heartbeat_if_missing(): void
    {
        $this->assertDatabaseMissing(Heartbeat::class, [
            'type' => HeartbeatType::Job,
        ]);

        (new HeartbeatJob())->handle();

        $this->assertDatabaseHas(Heartbeat::class, [
            'type' => HeartbeatType::Job,
        ]);
    }

    #[Test]
    public function it_updates_existing_job_heartbeat(): void
    {
        $this->travelTo(now()->subMinutes(10));

        $heartbeat = Heartbeat::create([
            'type' => HeartbeatType::Job,
        ]);

        $this->travelBack();

        (new HeartbeatJob())->handle();

        $this->assertTrue($heartbeat->refresh()->updated_at->isSameMinute(now()));
    }
}
