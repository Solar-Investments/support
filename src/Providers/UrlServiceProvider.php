<?php

declare(strict_types=1);

namespace SolarInvestments\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class UrlServiceProvider extends ServiceProvider
{
    protected string $rootUrl;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->rootUrl = Str::of(config('app.url'))
            ->rtrim('/')
            ->replace('http:', 'https:')
            ->toString();
    }

    public function boot(): void
    {
        if ($this->app->isLocal()) {
            return;
        }

        URL::forceScheme('https');
        URL::forceRootUrl($this->rootUrl);
    }
}
