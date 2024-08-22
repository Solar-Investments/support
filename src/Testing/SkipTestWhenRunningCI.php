<?php

declare(strict_types=1);

namespace SolarInvestments\Testing;

use Illuminate\Foundation\Testing\TestCase;

/**
 * @mixin TestCase
 */
trait SkipTestWhenRunningCI
{
    protected function setUpSkipTestWhenRunningCI(): void
    {
        if (app()->runningCI()) {
            $this->markTestSkipped('Test skipped when running in a CI environment.');
        }
    }
}
