<?php

namespace Unit\Application\Interactors\Twitter;

use App\Adapters\SqlModelAdapter;
use App\Application\InputData\BigQuerySqlModel;
use App\Application\Interactors\Twitter\TwitterApiManager;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Application\Repositories\TwitterApiRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Entities\ResponseData\Bigquery\BigQueryData;
use App\Entities\Twitter\TwitterMentionDataList;
use App\Entities\Twitter\TwitterMetaData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class TwitterApiManagerTest extends TestCase
{
    /**
     * getLatestData
     * @test
     * @dataProvider getLatestDataProvider
     *
     * @param  mixed $existsTable
     * @param  mixed $response
     * @param  mixed $expected
     * @return void
     */
    public function getLatestData($existsTable, $responseData, $expected): void
    {
        $repository = Mockery::mock(TwitterApiRepository::class);
        Mockery::mock('alias:' . Config::class)->shouldReceive(['get' => '']);

        if (is_null($responseData) === true) {
            $response = null;
        } else {
            $response = Mockery::mock(BigQueryData::class)
                ->shouldReceive($responseData)
                ->getMock();
        }

        $bigQueryUseCace = Mockery::mock(BigQueryUseCase::class);
        $bigQueryUseCace->shouldReceive(
            [
                'existsTable' => $existsTable,
                'getProjectId' => '',
                'getDatasetId' => '',
                'getTableId' => '',
                'getData' => $response,
            ]
        )->getMock();

        $model = Mockery::mock(BigQuerySqlModel::class);
        Mockery::mock('alias:' . SqlModelAdapter::class)
            ->shouldReceive(['getLatestCommentSql' => $model])->atMost()->once();

        $manager = new TwitterApiManager($repository, $bigQueryUseCace);
        $actual = $manager->getLatestData('title', 'en');

        $this->assertEquals($expected, $actual);
    }

    public function getLatestDataProvider(): array
    {
        return [
            'table not exitss case' => [
                'existsTable' => false,
                'responseData' => null,
                'expected' => null,
            ],
            'response is null case' => [
                'existsTable' => true,
                'responseData' => null,
                'expected' => null,
            ],
            'response has error case' => [
                'existsTable' => true,
                'responseData' =>
                [
                    'hasError' => true,
                    'getErrorMessage' => '',
                ],
                'expected' => null,
            ],
            'response count 0 case' => [
                'existsTable' => true,
                'responseData' =>
                [
                    'hasError' => false,
                    'getDataList->count' => 0,
                ],
                'expected' => null,
            ],
            'created_at is undefind case' => [
                'existsTable' => true,
                'responseData' =>
                [
                    'hasError' => false,
                    'getDataList->count' => 1,
                    'getDataList->first' => []
                ],
                'expected' => null,
            ],
            'normal case' => [
                'existsTable' => true,
                'responseData' =>
                [
                    'hasError' => false,
                    'getDataList->count' => 1,
                    'getDataList->first' => ['created_at' => '2021-01-01']
                ],
                'expected' => '2021-01-01',
            ]
        ];
    }

    /**
     * getTwitterMentionList
     * @test
     * @dataProvider getTwitterMentionListDataProvider
     *
     * @param  mixed $count
     * @param  mixed $pushData
     * @param  mixed $hasError
     * @param  mixed $paginationToken
     * @param  mixed $createdAt
     * @return void
     */
    public function getTwitterMentionList($count, $pushData, $hasError, $paginationToken, $createdAt): void
    {
        Log::shouldReceive('debug');
        if ($hasError === true) {
            Log::shouldReceive('error');
        }

        $expected = collect([]);
        $mentionList = collect([]);
        for ($i = 0; $i < $count; $i++) {
            $data = $pushData[$i] ?? $pushData[0];
            $mentionList->push($data);

            if (is_null($createdAt) === true) {
                $expected->push($data);
            } else {
                $targetDt = $pushData[$i]['created_at'] ?? $createdAt;

                $createdAtDate = new Carbon($createdAt);
                $targetDtDate = new Carbon($targetDt);
                if ($createdAtDate->gt($targetDtDate)) {
                    $expected->push($pushData[$i]);
                }
            }
        }

        // APIリクエストを２回以上行う
        $mentionListLoopData = null;
        if (is_null($paginationToken) === false) {
            $expected = collect([]);
            $mentionListLoopData = collect([]);
            for ($i = 0; $i < 510; $i++) {
                $createdAtDate = new Carbon('2021-01-01');
                $pushData = ['created_at' => $createdAtDate->toDateString()];

                $mentionListLoopData->push($pushData);

                if (is_null($createdAt) === true) {
                    $expected->push($pushData);
                } else {
                    $targetDt = $pushData ?? $createdAt;

                    $createdAtDate = new Carbon($createdAt);
                    $targetDtDate = new Carbon($targetDt);
                    if ($createdAtDate->gt($targetDtDate)) {
                        $expected->push($pushData);
                    }
                }
            }
        }

        $metaData = Mockery::mock(TwitterMetaData::class)->shouldReceive(
            ['getNextToken' => $paginationToken]
        )->getMock();

        $response = Mockery::mock('alias:' . TwitterMentionDataList::class);
        $response->shouldReceive(
            [
                'getMetaData' => $metaData,
                'hasError' => $hasError,
            ]
        );
        if (is_null($paginationToken) === true) {
            $response->shouldReceive('getMentionList')
                ->andReturn($mentionList);
        } else {
            $response->shouldReceive('getMentionList')
                ->andReturnValues([$mentionList, $mentionListLoopData]);
        }
        if ($hasError === true) {
            $response->shouldReceive('getErrorMessage')->once();
        }


        $repository = Mockery::mock(TwitterApiRepository::class);
        $repository->shouldReceive(
            [
                'getMentions' => $response,
            ]
        );

        $bigQueryUseCace = Mockery::mock(BigQueryUseCase::class);

        $manager = new TwitterApiManager($repository, $bigQueryUseCace);
        $actual = $manager->getTwitterMentionList('userId', $createdAt);

        $this->assertEquals($expected->count(), $actual->count());
    }

    public function getTwitterMentionListDataProvider(): array
    {
        return [
            'mentionList count orver 500 case' => [
                'count' => 501,
                'pushData' => [
                    [
                        'created_at' => '',
                    ]
                ],
                'hasError' => false,
                'paginationToken' => null,
                'createdAt' => null,
            ],
            'isSlice true case' => [
                'count' => 5,
                'pushData' => [
                    [
                        'created_at' => '2021-01-01',
                    ],
                    [
                        'created_at' => '2021-02-01',
                    ],
                    [
                        'created_at' => '2021-03-01',
                    ],
                    [
                        'created_at' => '2021-04-01',
                    ],
                    [
                        'created_at' => '2021-05-01',
                    ],

                ],
                'hasError' => false,
                'paginationToken' => null,
                'createdAt' => '2021-03-01',
            ],
            'has not error and paginationToken is null case' => [
                'count' => 5,
                'pushData' => [
                    [
                        'created_at' => '2021-01-01',
                    ],
                    [
                        'created_at' => '2021-02-01',
                    ],
                    [
                        'created_at' => '2021-03-01',
                    ],
                    [
                        'created_at' => '2021-04-01',
                    ],
                    [
                        'created_at' => '2021-05-01',
                    ],

                ],
                'hasError' => false,
                'paginationToken' => null,
                'createdAt' => null,
            ],
            'has not error and paginationToken is not null case' => [
                'count' => 5,
                'pushData' => [
                    [
                        'created_at' => '2021-01-01',
                    ],
                    [
                        'created_at' => '2021-02-01',
                    ],
                    [
                        'created_at' => '2021-03-01',
                    ],
                    [
                        'created_at' => '2021-04-01',
                    ],
                    [
                        'created_at' => '2021-05-01',
                    ],

                ],
                'hasError' => false,
                'paginationToken' => 'page toke',
                'createdAt' => null,
            ],
            'has error case' => [
                'count' => 5,
                'pushData' => [
                    [
                        'created_at' => '2021-01-01',
                    ],
                    [
                        'created_at' => '2021-02-01',
                    ],
                    [
                        'created_at' => '2021-03-01',
                    ],
                    [
                        'created_at' => '2021-04-01',
                    ],
                    [
                        'created_at' => '2021-05-01',
                    ],

                ],
                'hasError' => true,
                'paginationToken' => null,
                'createdAt' => null,
            ],
            'created_at is null case' => [
                'count' => 1,
                'pushData' => [
                    [
                        '2021-01-01',
                    ]

                ],
                'hasError' => false,
                'paginationToken' => null,
                'createdAt' => null,
                'expected' => '',
            ],
            'created_at is not null case' => [
                'count' => 5,
                'pushData' => [
                    [
                        'created_at' => '2021-01-01',
                    ],
                    [
                        'created_at' => '2021-02-01',
                    ],
                    [
                        'created_at' => '2021-03-01',
                    ],
                    [
                        'created_at' => '2021-04-01',
                    ],
                    [
                        'created_at' => '2021-05-01',
                    ],

                ],
                'hasError' => false,
                'paginationToken' => null,
                'createdAt' => '2021-03-01',
            ]
        ];
    }

    /**
     * slice
     * @test
     * @dataProvider sliceDataProvider
     *
     * @param  mixed $createdAt
     * @param  mixed $responseData
     * @param  mixed $expected
     * @return void
     */
    public function slice($createdAt, $responseData, $expected): void
    {
        Log::shouldReceive('debug');
        $repository = Mockery::mock(TwitterApiRepository::class);
        $bigQueryUseCace = Mockery::mock(BigQueryUseCase::class);

        $manager = new TwitterApiManager($repository, $bigQueryUseCace);
        $actual = $manager->slice($responseData, $createdAt);

        $this->assertEquals($expected['isSlice'], $actual['isSlice']);
        $this->assertEquals($expected['data']->count(), $actual['data']->count());
    }

    public function sliceDataProvider(): array
    {
        return [
            'createdAt is null case' => [
                'createdAt' => null,
                'responseData' => collect([
                    [
                        'created_at' => '2021-01-01',
                    ],
                    [
                        'created_at' => '2021-02-01',
                    ],
                    [
                        'created_at' => '2021-03-01',
                    ],
                    [
                        'created_at' => '2021-04-01',
                    ],
                    [
                        'created_at' => '2021-05-01',
                    ],

                ]),
                'expected' => [
                    'isSlice' => false,
                    'data' => collect([
                        [
                            'created_at' => '2021-01-01',
                        ],
                        [
                            'created_at' => '2021-02-01',
                        ],
                        [
                            'created_at' => '2021-03-01',
                        ],
                        [
                            'created_at' => '2021-04-01',
                        ],
                        [
                            'created_at' => '2021-05-01',
                        ],

                    ])
                ]
            ],
            'filter slice case' => [
                'createdAt' => '2021-03-01',
                'responseData' => collect([
                    [
                        'created_at' => '2021-01-01',
                    ],
                    [
                        'created_at' => '2021-02-01',
                    ],
                    [
                        'created_at' => '2021-03-01',
                    ],
                    [
                        'created_at' => '2021-04-01',
                    ],
                    [
                        'created_at' => '2021-05-01',
                    ],

                ]),
                'expected' => [
                    'isSlice' => true,
                    'data' => collect([
                        [
                            'created_at' => '2021-04-01',
                        ],
                        [
                            'created_at' => '2021-05-01',
                        ],

                    ])
                ]
            ],
            'not slice case' => [
                'createdAt' => '2020-12-01',
                'responseData' => collect([
                    [
                        'created_at' => '2021-01-01',
                    ],
                    [
                        'created_at' => '2021-02-01',
                    ],
                    [
                        'created_at' => '2021-03-01',
                    ],
                    [
                        'created_at' => '2021-04-01',
                    ],
                    [
                        'created_at' => '2021-05-01',
                    ],

                ]),
                'expected' => [
                    'isSlice' => false,
                    'data' => collect([
                        [
                            'created_at' => '2021-01-01',
                        ],
                        [
                            'created_at' => '2021-02-01',
                        ],
                        [
                            'created_at' => '2021-03-01',
                        ],
                        [
                            'created_at' => '2021-04-01',
                        ],
                        [
                            'created_at' => '2021-05-01',
                        ],

                    ])
                ]
            ]
        ];
    }
}
