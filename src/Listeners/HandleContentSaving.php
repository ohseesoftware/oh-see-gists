<?php

namespace OhSeeSoftware\OhSeeGists\Listeners;

use GrahamCampbell\GitHub\GitHubManager;
use Statamic\Events\Data\EntrySaving;

class HandleContentSaving
{
    private $github;

    public function __construct(GitHubManager $github)
    {
        $this->github = $github;
    }

    public function handle(EntrySaving $event)
    {
        $githubToken = config('github.connections.main.token', null);
        if (empty($githubToken)) {
            return;
        }

        $data = $event->data->data();
        $content = $data->get('content', []);

        $gistBlocks = $this->getGistBlocks($content);

        if (empty($gistBlocks)) {
            return;
        }

        $title = $data['title'] ?? 'Created by Oh See Gists add-on';

        $gistData = $this->buildGistData($gistBlocks, $title);
        $this->saveGist($gistData, $gistBlocks);

        $data->put('content', $content);
    }

    private function buildGistData(array &$gistBlocks = [], string $title): array
    {
        $gistData = [
            'public' => true,
            'description' => $title,
            'files' => [],
        ];

        foreach ($gistBlocks as &$gistBlock) {
            $extension = $gistBlock['extension'] ?? 'txt';

            $filename = $gistBlock['gist_filename'] ?? null;
            if (!$filename) {
                $filename = uniqid() . '.' . $extension;
                $gistBlock['gist_filename'] = $filename;
            }

            $gistData['files'][$filename] = [
                'content' => $gistBlock['code']
            ];
        }

        return $gistData;
    }

    private function saveGist(array $gistData, array &$gistBlocks): void
    {
        $gistId = null;
        foreach ($gistBlocks as &$gistBlock) {
            if (empty($gistId) && !empty($gistBlock['gist_id'])) {
                $gistId = $gistBlock['gist_id'];
            }
        }

        if (!empty($gistData['files'])) {
            if (empty($gistId)) {
                // Create a new Gist
                $response = $this->github->gists()->create($gistData);

                foreach ($gistBlocks as &$gistBlock) {
                    $gistBlock['gist_id'] = $response['id'];
                }
            } else {
                // Update existing Gist
                $this->github->gists()->update($gistId, $gistData);
            }
        }
    }

    private function getGistBlocks(array &$content): array
    {
        $gistBlocks = [];

        foreach ($content as &$block) {
            $type = $block['type'] ?? null;
            if ($type !== 'set') {
                continue;
            }

            $type = $block['attrs']['values']['type'] ?? null;
            $code = $block['attrs']['values']['code'] ?? null;

            if ($type === 'gist_content' || !empty($code)) {
                $gistBlocks[] = &$block['attrs']['values'];
            }
        }

        return $gistBlocks;
    }
}
