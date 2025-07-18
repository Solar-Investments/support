<?php

declare(strict_types=1);

namespace SolarInvestments\Services;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Monolog\Formatter\GoogleCloudLoggingFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

class Helio
{
    public static function bootstrapperBootstrapping(Application $app, string $bootstrapper): void
    {
        //
    }

    public static function bootstrapperBootstrapped(Application $app, string $bootstrapper): void
    {
        (match ($bootstrapper) {
            LoadConfiguration::class => static function () use ($app): void {
                //
            },
            HandleExceptions::class => static function () use ($app): void {
                static::configureLogging($app);
            },
            default => static fn () => true,
        })();
    }

    public static function configureLogging(Application $app): void
    {
        if ($app->isLocal()) {
            return;
        }

        /** @var Repository $config */
        $config = $app['config'];

        $config->set('logging.default', 'stderr');

        $config->set('logging.channels.stderr', [
            'driver' => 'monolog',
            'level' => $app->isProduction() ? 'warning' : 'debug',
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => GoogleCloudLoggingFormatter::class,
            'formatter_with' => [
                'includeStacktraces' => true,
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ]);
    }
}
