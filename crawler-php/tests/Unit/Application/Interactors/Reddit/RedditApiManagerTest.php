<?php

namespace Unit\Application\Interactors\Reddit;

use App\Application\Interactors\Reddit\RedditApiManager;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Application\Repositories\RedditApiRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\LatestData;
use App\Entities\ResponseData\Bigquery\BigQueryData;
use App\Entities\Reddit\SubReddit;
use App\Entities\Reddit\Thread;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class RedditApiManagerTest extends TestCase
{
    /**
     * getLatestData
     * @test
     * @dataProvider getLatestDataProvider
     *
     * @param  mixed $existsTable
     * @param  mixed $getData
     * @param  mixed $hasError
     * @param  mixed $count
     * @param  mixed $first
     * @param  mixed $expected
     * @return void
     */
    public function getLatestData(bool $existsTable, ?array $getData, bool $hasError, int $count, ?Collection $first, ?LatestData $expected): void
    {
        $response = Mockery::mock(BigQueryData::class);

        if ($existsTable === false) {
            Mockery::mock('alias:' . Config::class)
                ->shouldReceive(['get' => ''])
                ->never();

            $response->shouldReceive(
                [
                    'hasError',
                    'getDataList->count',
                    'getDataList',
                    'getErrorMessage',
                ]
            )->never();

            $response->shouldReceive(['getData'])->never();
        } else {
            Mockery::mock('alias:' . Config::class)
                ->shouldReceive(['get' => ''])
                ->with('crawl.name')
                ->once();

            if (is_null($getData) === true) {
                $response->shouldReceive(
                    [
                        'hasError',
                        'getDataList->count',
                        'getDataList',
                        'getErrorMessage',
                    ]
                )->never();
            } elseif ($hasError === true) {
                Log::shouldReceive('error');
                $response->shouldReceive(['hasError' => $hasError, 'getErrorMessage' => ''])->once();
                $response->shouldReceive(
                    [
                        'getDataList->count',
                        'getDataList',
                    ]
                )->never();
            } elseif ($count === 0) {
                $response->shouldReceive(
                    [
                        'hasError' => $hasError,
                        'getDataList->count' => $count,
                    ]
                )->once();
                $response->shouldReceive(['getDataList->first', 'getErrorMessage'])->never();
            } else {
                $response->shouldReceive(
                    [
                        'hasError' => $hasError,
                        'getDataList' => $first,
                    ]
                );
                // ->once();
                $response->shouldReceive(['getErrorMessage'])->never();
            }
        }

        if (is_null($getData) === true) {
            $getDataResponse = $getData;
        } else {
            $getDataResponse = $response;
        }

        $bigQueryUseCace = Mockery::mock(BigQueryUseCase::class);
        $bigQueryUseCace->shouldReceive(
            [
                'existsTable' => $existsTable,
                'getProjectId' => '',
                'getDatasetId' => '',
                'getTableId' => '',
                'getData' => $getDataResponse,
            ]
        )->getMock();

        $repository = Mockery::mock(RedditApiRepository::class);

        $manager = new RedditApiManager($repository, $bigQueryUseCace);
        $actual  = $manager->getLatestData('title', 'language');

        $this->assertEquals($expected, $actual);
    }

    public function getLatestDataProvider(): array
    {
        return [
            'existsTable is false case' => [
                'existsTable' => false,
                'getData' => [],
                'hasError' => false,
                'count' => 1,
                'first' => collect([['created_at' => 'yyyy-mm-dd hh:ii:ss']]),
                'expected' => null,
            ],
            'getData is null case' => [
                'existsTable' => true,
                'getData' => null,
                'hasError' => false,
                'count' => 1,
                'first' => collect([['created_at' => 'yyyy-mm-dd hh:ii:ss']]),
                'expected' => null,
            ],
            'response hasError is true case' => [
                'existsTable' => true,
                'getData' => [],
                'hasError' => true,
                'count' => 1,
                'first' => collect([['created_at' => 'yyyy-mm-dd hh:ii:ss']]),
                'expected' => null,
            ],
            'response getDataList count is 0 case' => [
                'existsTable' => true,
                'getData' => [],
                'hasError' => false,
                'count' => 0,
                'first' => collect([['created_at' => 'yyyy-mm-dd hh:ii:ss']]),
                'expected' => null,
            ],
            'response getDataList created_at is undefined case' => [
                'existsTable' => true,
                'getData' => [
                    []
                ],
                'hasError' => false,
                'count' => 1,
                'first' => collect([['id' => 'id-xxxx']]),
                'expected' => new LatestData('', ['id' => new Colmun('id', 'id-xxxx')]),
            ],
            'normal case' => [
                'existsTable' => true,
                'getData' => [
                    [
                        'created_at' => 'yyyy-mm-dd hh:ii:ss'
                    ]
                ],
                'hasError' => false,
                'count' => 1,
                'first' => collect([[
                    'id' => 'id-xxxx',
                    'created_at' => 'yyyy-mm-dd hh:ii:ss'
                ]]),
                'expected' =>
                new LatestData(
                    '',
                    [
                        'id' => new Colmun('id', 'id-xxxx'),
                        'created_at' => new Colmun('created_at', 'yyyy-mm-dd hh:ii:ss')
                    ]
                ),
            ],
        ];
    }

    /**
     * getThreadList
     * @test
     * @dataProvider getThreadListDataProvider
     *
     * @param  bool $hasError
     * @param  array $apiResponseData
     * @param  Collection|null $expected
     * @return void
     */
    public function getThreadList(bool $hasError, array $apiResponseData, ?Collection $expected): void
    {
        Log::shouldReceive('debug');
        if ($hasError === true) {
            Log::shouldReceive('error');
        }

        $subReddit = new SubReddit($apiResponseData);

        $repository = Mockery::mock(RedditApiRepository::class);
        $repository->shouldReceive(
            [
                'getSubReddit' => $subReddit,
            ]
        )->getMock();

        $bigQueryUseCace = Mockery::mock(BigQueryUseCase::class);

        $manager = new RedditApiManager($repository, $bigQueryUseCace);
        $actual  = $manager->getThreadList('id');

        $this->assertEquals($expected, $actual);
    }

    public function getThreadListDataProvider(): array
    {
        return [
            'threadList count 0 case' => [
                'hasError' => false,
                [
                    'data' => [
                        'children' => [
                            [
                                'data' => [
                                    'subreddit' => 'page name',
                                    'title' => 'thread title',
                                    'selftext' => 'text',
                                    'permalink' => 'https://hoge.fuga/testdata'
                                ]
                            ]
                        ]
                    ]
                ],
                'expected' => null
            ],
            'normal case' => [
                'hasError' => false,
                'apiResponseData' => [
                    'data' => [
                        'children' => [
                            [
                                'data' => [
                                    'subreddit' => 'page name',
                                    'title' => 'thread title',
                                    'selftext' => 'text',
                                    'permalink' => '/testdata'
                                ]
                            ]
                        ]
                    ]
                ],
                'expected' => collect(
                    [
                        new Thread('thread title', 'text', 'https://www.reddit.com/testdata')
                    ]
                )
            ],
        ];
    }

    /**
     * getCommentList
     * @test
     * @dataProvider getCommentListDataProvider
     *
     * @param  mixed $createdAt
     * @param  mixed $responseThreadArray
     * @param  mixed $expectedArray
     * @return void
     */
    public function getCommentList(?Colmun $createdAt, array $responseThreadArray, $expectedArray): void
    {
        $expected = collect($expectedArray);

        Log::shouldReceive('debug', 'info');

        $requestThread = new Thread('title', 'text', 'url');
        $requestThreadList = collect([$requestThread]);

        $thread = new Thread('title', 'text', 'url');
        $thread->setComments($responseThreadArray);

        $repository = Mockery::mock(RedditApiRepository::class);
        $repository->shouldReceive(
            [
                'getComment' => $thread,
            ]
        )->getMock();

        $bigQueryUseCace = Mockery::mock(BigQueryUseCase::class);

        $manager = new RedditApiManager($repository, $bigQueryUseCace);
        $actual  = $manager->getCommentList($requestThreadList, $createdAt);

        $this->assertEquals($expected, $actual);
    }

    public function getCommentListDataProvider(): array
    {
        $created = Carbon::parse('2020-12-01', 'Asia/Tokyo');
        $older = Carbon::parse('2019-12-01', 'Asia/Tokyo');

        return [
            'createdAt is null and duplicat id and test empty case' => [
                'createdAt' => null,
                'responseThreadArray' => [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => '',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => '',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [],
            ],
            'createdAt is null and duplicat id and test empty or undefind body case' => [
                'createdAt' => null,
                'responseThreadArray' => [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => '',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [],
            ],
            'createdAt is null and duplicat id case' => [
                'createdAt' => null,
                'responseThreadArray' => [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text1',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text2',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id-1',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text1',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ],
            'createdAt is null and filter by key name case' => [
                'createdAt' => null,
                'responseThreadArray' => [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id-2',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ],
            'createdAt is null and filter by empty text case' => [
                'createdAt' => null,
                'responseThreadArray' =>
                [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => '',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id-2',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ],
            'createdAt is null and filter by "empty text and key name" case' => [
                'createdAt' => null,
                'commentListArray' =>
                [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => '',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-3',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id-3',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ],
            'createdAt is null and not filter case' => [
                'createdAt' => null,
                'responseThreadArray' =>
                [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text1',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text2',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id-1',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text1',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                    [
                        'id' => 'id-2',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text2',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ],
            'createdAt is not null and duplicate id case' => [
                'createdAt' => new Colmun('created', $older->getTimestamp()),
                'responseThreadArray' =>
                [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text1',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text2',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id-1',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text1',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ],
            'createdAt is not null and not filter data case' => [
                'createdAt' => new Colmun('created', $older->getTimestamp()),
                'responseThreadArray' =>
                [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text1',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text2',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id-1',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text1',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                    [
                        'id' => 'id-2',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text2',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ],

            'createdAt is not null and filter by text key case' => [
                'createdAt' => new Colmun('created', '2020-01-01'),
                'responseThreadArray' =>
                [
                    [
                        'data' => [
                            'id' => 'id',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text2',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text2',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ],
            'createdAt is not null and filter by empty text case' => [
                'createdAt' => new Colmun('created', '2020-01-01'),
                'responseThreadArray' =>
                [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => '',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text2',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id-2',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text2',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ],
            'createdAt is not null and filter by created_at case' => [
                'createdAt' => new Colmun('created', '2020-01-01'),
                'responseThreadArray' =>
                [
                    [
                        'data' => [
                            'id' => 'id-1',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => '',
                            'permalink' => '/hoge',
                            'created' => $older->getTimestamp(),
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 'id-2',
                            'parent_id' => 'parent_id',
                            'subreddit' => 'subreddit',
                            'body' => 'text2',
                            'permalink' => '/hoge',
                            'created' => $created->getTimestamp(),
                        ],
                    ],
                ],
                'expectedArray' => [
                    [
                        'id' => 'id-2',
                        'parent_id' => 'parent_id',
                        'subreddit' => 'subreddit',
                        'text' => 'text2',
                        'permalink' => '/hoge',
                        'created_at' => $created->format('Y-m-d H:i:s'),
                        'created' => $created->getTimestamp(),
                        'date' => $created->format('Y-m-d'),
                    ],
                ],
            ]
        ];
    }
}
