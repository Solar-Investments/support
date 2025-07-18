<?php

declare(strict_types=1);

namespace SolarInvestments\Console\Commands;

use Illuminate\Console\Command;
use SolarInvestments\Enums\HeartbeatType;
use SolarInvestments\Models\Heartbeat;

class HeartbeatCommand extends Command
{
    protected $signature = 'healthcheck:heartbeat';

    protected $description = 'Update heartbeat timestamp';

    public function handle(): int
    {
        Heartbeat::firstOrNew([
            'type' => HeartbeatType::Schedule,
        ])->touch();

        return static::SUCCESS;
    }
}
