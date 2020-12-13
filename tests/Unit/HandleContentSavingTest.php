<?php

namespace OhSeeSoftware\OhSeeGists\Tests\Unit;

use Illuminate\Support\Arr;
use Mockery;
use Mockery\MockInterface;
use OhSeeSoftware\OhSeeGists\Listeners\HandleContentSaving;
use OhSeeSoftware\OhSeeGists\Tests\Stubs\GitHubGistsStub;
use OhSeeSoftware\OhSeeGists\Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Statamic\Entries\Entry;
use Statamic\Events\EntrySaving;

class HandleContentSavingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config([
            'github.connections.main.token' => 'fake-token'
        ]);

        $this->entry = new Entry();
        $this->entry->data([
            'title' => 'Fake entry title',
            'content' => [
                [
                    'type' => 'set',
                    'attrs' => [
                        'values' => [
                            'type' => 'gist_content',
                            'extension' => 'php',
                            'code' => 'echo "hello!"'
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_listens_for_entry_saving_event()
    {
        // Given
        $this->partialMock(HandleContentSaving::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle')->once();
        });

        // When
        event(new EntrySaving($this->entry));
    }

    /** @test */
    public function it_sends_request_to_github_to_create_new_gist()
    {
        // Given
        Mockery::mock('alias:' . Uuid::class, [
            'uuid4' => 'some-fake-uuid',
        ]);

        $this->partialMock(GitHubGistsStub::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->with([
                'public' => true,
                'description' => 'Fake entry title',
                'files' => [
                    'some-fake-uuid.php' => [
                        'content' => 'echo "hello!"'
                    ]
                ],
            ])->andReturn([
                'id' => '12345'
            ])->atleast()->once();
        });

        // When
        event(new EntrySaving($this->entry));

        // Then
        $expected = Arr::get($this->entry->data(), 'content.0.attrs.values.gist_id');
        $this->assertEquals('12345', $expected);
    }

    /** @test */
    public function it_creates_multiple_files_per_gist_per_entry()
    {
        // Given
        Mockery::mock('alias:' . Uuid::class, [
            'uuid4' => 'some-fake-uuid',
        ]);

        $this->entry->data([
            'title' => 'Fake entry title',
            'content' => [
                [
                    'type' => 'set',
                    'attrs' => [
                        'values' => [
                            'type' => 'gist_content',
                            'extension' => 'php',
                            'code' => 'echo "hello!"'
                        ]
                    ]
                ],
                [
                    'type' => 'set',
                    'attrs' => [
                        'values' => [
                            'type' => 'gist_content',
                            'extension' => 'txt',
                            'code' => 'Some text'
                        ]
                    ]
                ]
            ]
        ]);

        $this->partialMock(GitHubGistsStub::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->with([
                'public' => true,
                'description' => 'Fake entry title',
                'files' => [
                    'some-fake-uuid.php' => [
                        'content' => 'echo "hello!"'
                    ],
                    'some-fake-uuid.txt' => [
                        'content' => 'Some text'
                    ]
                ],
            ])->atleast()->once();
        });

        // When
        event(new EntrySaving($this->entry));
    }

    /** @test */
    public function it_sends_request_to_github_to_update_existing_gist()
    {
        // Given
        Mockery::mock('alias:' . Uuid::class, [
            'uuid4' => 'some-fake-uuid',
        ]);

        $this->entry->data([
            'title' => 'Fake entry title',
            'content' => [
                [
                    'type' => 'set',
                    'attrs' => [
                        'values' => [
                            'type' => 'gist_content',
                            'extension' => 'php',
                            'code' => 'echo "hello!"',
                            'gist_filename' => 'file.php',
                            'gist_id' => '12345'
                        ]
                    ]
                ],
            ]
        ]);

        $this->partialMock(GitHubGistsStub::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')->with('12345', [
                'public' => true,
                'description' => 'Fake entry title',
                'files' => [
                    'file.php' => [
                        'content' => 'echo "hello!"'
                    ]
                ],
            ])->atleast()->once();
        });

        // When
        event(new EntrySaving($this->entry));
    }
}
