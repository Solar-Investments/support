<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Providers;

use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Monolog\Formatter\GoogleCloudLoggingFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use SolarInvestments\Services\Helio;
use SolarInvestments\Tests\TestCase;

class HelioServiceProviderTest extends TestCase
{
    #[Test]
    public function it_can_configure_logging(): void
    {
        Helio::bootstrapperBootstrapped($this->app, HandleExceptions::class);

        $this->assertSame('stderr', config('logging.default'));

        $channel = config('logging.channels.stderr');

        $this->assertIsArray($channel);
        $this->assertSame('monolog', $channel['driver']);
        $this->assertSame('php://stderr', $channel['handler_with']['stream']);
        $this->assertSame(StreamHandler::class, $channel['handler']);
        $this->assertSame(GoogleCloudLoggingFormatter::class, $channel['formatter']);
        $this->assertTrue($channel['formatter_with']['includeStacktraces']);
        $this->assertContains(PsrLogMessageProcessor::class, $channel['processors']);
    }

    #[Test]
    #[DefineEnvironment('production')]
    public function it_can_set_the_logging_level_to_warning_when_in_production(): void
    {
        Helio::bootstrapperBootstrapped($this->app, HandleExceptions::class);

        $this->assertSame('warning', config('logging.channels.stderr.level'));
    }

    #[Test]
    #[DefineEnvironment('local')]
    public function it_can_set_the_logging_level_to_debug_when_not_in_production(): void
    {
        Helio::bootstrapperBootstrapped($this->app, HandleExceptions::class);

        $this->assertSame('debug', config('logging.channels.stderr.level'));
    }

    #[Test]
    public function it_can_configure_statamic(): void
    {
        Helio::bootstrapperBootstrapped($this->app, LoadConfiguration::class);

        $commands = config('statamic.git.commands');

        $this->assertIsArray($commands);
        $this->assertCount(2, $commands);
        $this->assertSame('{{ git }} add {{ paths }}', $commands[0]);
        $this->assertStringContainsString('-c user.name="{{ name }}"', $commands[1]);
        $this->assertStringContainsString('-c user.email="{{ email }}"', $commands[1]);
        $this->assertStringContainsString('-m "environment=testing"', $commands[1]);
        $this->assertStringContainsString('-m "project=helio-platform"', $commands[1]);
        $this->assertStringContainsString('-m "user.email={{ email }}"', $commands[1]);
        $this->assertStringContainsString('-m "user.name={{ name }}"', $commands[1]);
    }
}
