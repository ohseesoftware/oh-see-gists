<?php

namespace OhSeeSoftware\OhSeeGists;

use OhSeeSoftware\OhSeeGists\Listeners\HandleContentSaving;
use Statamic\Events\Data\EntrySaving;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        EntrySaving::class => [
            HandleContentSaving::class
        ]
    ];

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/github.php' => config_path('github.php')
            ], 'oh-see-gists');

            $this->publishes([
                __DIR__.'/../resources/fieldsets' => resource_path('fieldsets')
            ], 'oh-see-gists');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views')
            ], 'oh-see-gists');
        }
    }
}
