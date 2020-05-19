<?php

namespace OhSeeSoftware\OhSeeGists\Listeners;

use GrahamCampbell\GitHub\GitHubManager;
use Illuminate\Support\Facades\Log;
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

        try {
            $data = $event->data->data();
            $content = $data->get('content', []);

            $gistBlocks = $this->getGistBlocks($content);
            
            if (empty($gistBlocks)) {
                return;
            }
    
            $title = $data->get('title', 'Created by Oh See Gists add-on');
    
            $gistData = $this->buildGistData($gistBlocks, $title);
            $this->saveGist($gistData, $gistBlocks);
    
            $data->put('content', $content);
        } catch (\Throwable $e) {
            Log::error("Error saving gist blocks");
            Log::error($e);
        }
    }

    private function buildGistData(array &$gistBlocks, string $title): array
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
        if (empty($gistData['files'])) {
            return;
        }

        $gistId = $this->getGistIdFromBlocks($gistBlocks);

        if (empty($gistId)) {
            $response = $this->createGist($gistData, $gistBlocks);
        } else {
            $response = $this->updateGist($gistId, $gistData);
        }

        $this->updateGistIds($response['id'], $gistBlocks);
    }

    private function getGistIdFromBlocks(array $gistBlocks): ?string
    {
        foreach ($gistBlocks as &$gistBlock) {
            $gistBlockId = $gistBlock['gist_id'] ?? null;

            if ($gistBlockId) {
                return $gistBlockId;
            }
        }

        return null;
    }

    private function createGist(array $gistData, array &$gistBlocks): array
    {
        return $this->github->gists()->create($gistData);
    }

    private function updateGist(string $gistId, array $gistData): array
    {
        return $this->github->gists()->update($gistId, $gistData);
    }

    private function updateGistIds(string $gistId, array &$gistBlocks): void
    {
        foreach ($gistBlocks as &$gistBlock) {
            $gistBlock['gist_id'] = $gistId;
        }
    }

    private function getGistBlocks(array &$content): array
    {
        $gistBlocks = [];

        foreach ($content as &$block) {
            $type = $block['type'] ?? null;
            $setType = $block['attrs']['values']['type'] ?? null;

            if ($type !== 'set' || $setType !== 'gist_content') {
                continue;
            }

            $gistBlocks[] = &$block['attrs']['values'];
        }

        return $gistBlocks;
    }
}
