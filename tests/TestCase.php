<?php

namespace OhSeeSoftware\OhSeeGists\Tests;

use GrahamCampbell\GitHub\GitHubManager;
use OhSeeSoftware\OhSeeGists\ServiceProvider;
use OhSeeSoftware\OhSeeGists\Tests\Stubs\GitHubManagerStub;
use Statamic\Extend\Manifest;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->bind(GitHubManager::class, GitHubManagerStub::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'ohseesoftware/oh-see-gists' => [
                'id' => 'ohseesoftware/oh-see-gists',
                'namespace' => 'OhSeeSoftware\\OhSeeGists\\',
            ],
        ];
    }
}
