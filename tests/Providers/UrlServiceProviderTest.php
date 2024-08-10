<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionException;
use SolarInvestments\Tests\TestCase;

class UrlServiceProviderTest extends TestCase
{
    #[Test]
    public function it_can_force_the_url_scheme(): void
    {
        /** @var UrlGenerator $url */
        $url = URL::getFacadeRoot();

        try {
            $forceScheme = (new ReflectionClass($url))->getProperty('forceScheme');
        } catch (ReflectionException) {
            $this->fail();
        }

        $this->assertSame('https://', $forceScheme->getValue($url));
    }

    #[Test]
    public function it_can_force_the_root_url(): void
    {
        /** @var UrlGenerator $url */
        $url = URL::getFacadeRoot();

        try {
            $forcedRoot = (new ReflectionClass($url))->getProperty('forcedRoot');
        } catch (ReflectionException) {
            $this->fail();
        }

        $this->assertSame('https://localhost', $forcedRoot->getValue($url));
    }
}
