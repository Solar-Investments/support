<?php

declare(strict_types=1);

namespace SolarInvestments\Enums;

enum HeartbeatType: int
{
    case Job = 1;
    case Schedule = 2;
}
