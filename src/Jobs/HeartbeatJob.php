<?php

declare(strict_types=1);

namespace SolarInvestments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SolarInvestments\Enums\HeartbeatType;
use SolarInvestments\Models\Heartbeat;

class HeartbeatJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        Heartbeat::firstOrNew([
            'type' => HeartbeatType::Job,
        ])->touch();
    }
}
