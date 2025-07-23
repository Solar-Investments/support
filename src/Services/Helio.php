<?php

declare(strict_types=1);

namespace SolarInvestments\Services;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Env;
use Monolog\Formatter\GoogleCloudLoggingFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

class Helio
{
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

    public static function configureStatamic(Application $app): void
    {
        /** @var Repository $config */
        $config = $app['config'];

        $name = Env::get('STATAMIC_GIT_USER_NAME', '{{ name }}');
        $email = Env::get('STATAMIC_GIT_USER_EMAIL', '{{ email }}');
        $project = Env::get('GCP_PROJECT_ID', 'helio-platform');

        $config->set('statamic.git.commands', [
            '{{ git }} add {{ paths }}',
            collect([
                '{{ git }}',
                '-c user.name="'.$name.'"',
                '-c user.email="'.$email.'"',
                'commit',
                '-m "[skip ci] {{ message }}"',
                '-m "environment='.$app['env'].'"',
                '-m "project='.$project.'"',
                '-m "user.email={{ email }}"',
                '-m "user.name={{ name }}"',
            ])->implode(' '),
        ]);
    }
}
