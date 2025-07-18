<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Console\Commands\HeartbeatCommand;
use SolarInvestments\Enums\HeartbeatType;
use SolarInvestments\Models\Heartbeat;
use SolarInvestments\Tests\TestCase;

class HeartbeatCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_schedule_heartbeat_if_missing(): void
    {
        $this->assertDatabaseMissing(Heartbeat::class, [
            'type' => HeartbeatType::Schedule,
        ]);

        $exitCode = (new HeartbeatCommand())->handle();
        $this->assertSame(HeartbeatCommand::SUCCESS, $exitCode);

        $this->assertDatabaseHas(Heartbeat::class, [
            'type' => HeartbeatType::Schedule,
        ]);
    }

    #[Test]
    public function it_updates_existing_schedule_heartbeat(): void
    {
        $this->travelTo(now()->subMinutes(10));

        $heartbeat = Heartbeat::create([
            'type' => HeartbeatType::Schedule,
        ]);

        $this->travelBack();

        $exitCode = (new HeartbeatCommand())->handle();
        $this->assertSame(HeartbeatCommand::SUCCESS, $exitCode);

        $this->assertTrue($heartbeat->refresh()->updated_at->isSameMinute(now()));
    }
}
