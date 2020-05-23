<?php

namespace OhSeeSoftware\OhSeeGists\Tests\Stubs;

use GrahamCampbell\GitHub\GitHubManager;

class GitHubManagerStub extends GitHubManager
{
    public function gists()
    {
        return resolve(GitHubGistsStub::class);
    }
}
