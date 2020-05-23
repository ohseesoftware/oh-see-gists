<?php

namespace OhSeeSoftware\OhSeeGists\Tests\Stubs;

use Github\Api\Gists;

class GitHubGistsStub extends Gists
{
    public function create(array $data): array
    {
        return [
            'id' => '12345'
        ];
    }

    public function update($gistId, array $data): array
    {
        return [
            'id' => '12345'
        ];
    }
}
