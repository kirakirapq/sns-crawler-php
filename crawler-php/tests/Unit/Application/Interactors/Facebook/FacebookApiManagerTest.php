<?php

namespace Unit\Application\Interactors\Facebook;

use App\Application\Interactors\Facebook\FacebookApiManager;
use App\Application\Repositories\FacebookApiRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\LatestData;
use App\Entities\ResponseData\Bigquery\BigQueryData;
use App\Entities\Facebook\FacebookDataList;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class FacebookApiManagerTest extends TestCase
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

        $repository = Mockery::mock(FacebookApiRepository::class);

        $manager = new FacebookApiManager($repository, $bigQueryUseCace);
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
     * getFeedList
     * @test
     * @dataProvider getFeedListDataProvider
     *
     * @param  bool $hasError
     * @param  array $apiResponseData
     * @param  Collection|null $expected
     * @return void
     */
    public function getFeedList(bool $hasNextPage, ?string $nextPage, bool $isEmpty, Collection $dataList, ?Collection $expected): void
    {
        Log::shouldReceive('debug');
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn('id', 'token');

        $fbFeedDataList = Mockery::mock(FacebookDataList::class)
            ->shouldReceive(
                [
                    'isEmpty' => $isEmpty,
                ]
            );

        if ($hasNextPage === true) {
            $fbFeedDataList->shouldReceive('getNextPage')->andReturn($nextPage, '');
            $fbFeedDataList->shouldReceive('hasNextPage')->andReturn(true, false);
        } else {
            $fbFeedDataList->shouldReceive('getNextPage')->andReturn($nextPage);
            $fbFeedDataList->shouldReceive('hasNextPage')->andReturn(false);
        }

        if ($isEmpty === true) {
            $fbFeedDataList->shouldReceive('getDataList');
        } else {
            $fbFeedDataList->shouldReceive(['getDataList' => $dataList]);
        }


        $repository = Mockery::mock(FacebookApiRepository::class)
            ->shouldReceive(['getFacebookDataList' => $fbFeedDataList->getMock()])
            ->getMock();

        $bigQueryUseCace = Mockery::mock(BigQueryUseCase::class);

        $manager = new FacebookApiManager($repository, $bigQueryUseCace);
        $actual  = $manager->getFeedList('title', 'language');

        $this->assertEquals($expected, $actual);
    }

    public function getFeedListDataProvider(): array
    {
        return [
            'next page does not have and empty case' => [
                'hasNextPage' => false,
                'nextPage' => null,
                'isEmpty' => true,
                'dataList' => collect([]),
                'expected' => null
            ],
            'next page does not have and not empty case' => [
                'hasNextPage' => false,
                'nextPage' => null,
                'isEmpty' => false,
                'dataList' => collect([
                    ['id' => 'fb-xxx1', 'date' => 'xxxx-xx-xx'],
                    ['id' => 'fb-xxx2', 'date' => 'xxxx-xx-xx']
                ]),
                'expected' => collect([
                    ['id' => 'fb-xxx1', 'date' => 'xxxx-xx-xx'],
                    ['id' => 'fb-xxx2', 'date' => 'xxxx-xx-xx']
                ]),
            ],
            'next page has and empty case' => [
                'hasNextPage' => true,
                'nextPage' => 'next',
                'isEmpty' => true,
                'dataList' => collect([]),
                'expected' => null
            ],
            'next page have and not empty case' => [
                'hasNextPage' => true,
                'nextPage' => 'next',
                'isEmpty' => false,
                'dataList' => collect([
                    ['id' => 'fb-xxx1', 'date' => 'xxxx-xx-xx'],
                    ['id' => 'fb-xxx2', 'date' => 'xxxx-xx-xx']
                ]),
                'expected' => collect([
                    ['id' => 'fb-xxx1', 'date' => 'xxxx-xx-xx'],
                    ['id' => 'fb-xxx2', 'date' => 'xxxx-xx-xx']
                ]),
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
    public function getCommentList(bool $hasNextPage, ?string $nextPage, bool $isEmpty, Collection $dataList, ?Colmun $createdAt, ?Collection $expected): void
    {
        Log::shouldReceive('debug', 'info');
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn('id', 'token');

        $fbFeedDataList = Mockery::mock(FacebookDataList::class)
            ->shouldReceive(
                [
                    'isEmpty' => $isEmpty,
                ]
            );

        if ($hasNextPage === true) {
            $fbFeedDataList->shouldReceive('getNextPage')->andReturn($nextPage, '');
            $fbFeedDataList->shouldReceive('hasNextPage')->andReturn(true, false);
        } else {
            $fbFeedDataList->shouldReceive('getNextPage')->andReturn($nextPage);
            $fbFeedDataList->shouldReceive('hasNextPage')->andReturn(false);
        }

        if ($isEmpty === true) {
            $fbFeedDataList->shouldReceive('getDataList');
        } else {
            $fbFeedDataList->shouldReceive(['getDataList' => $dataList]);
            Log::shouldReceive('info');
        }

        $repository = Mockery::mock(FacebookApiRepository::class)
            ->shouldReceive(
                [
                    'getFacebookDataList' => $fbFeedDataList->getMock()
                ]
            )->getMock();

        $bigQueryUseCace = Mockery::mock(BigQueryUseCase::class);

        $feeds = collect([['id' => 'xxx']]);
        $manager = new FacebookApiManager($repository, $bigQueryUseCace);
        $actual  = $manager->getCommentList('title', 'language', $feeds, $createdAt);

        $this->assertEquals($expected, $actual);
    }

    public function getCommentListDataProvider(): array
    {
        $created = Carbon::parse('2020-12-01', 'Asia/Tokyo');
        $older = Carbon::parse('2019-12-01', 'Asia/Tokyo');

        return [
            'hasNextPage is false and empty data case' => [
                'hasNextPage' => false,
                'nextPage' => null,
                'isEmpty' => true,
                'dataList' => collect([]),
                'createdAt' => null,
                'expected' => null,
            ],
            'hasNextPage is true and empty data case' => [
                'hasNextPage' => true,
                'nextPage' => 'next',
                'isEmpty' => true,
                'dataList' => collect([]),
                'createdAt' => null,
                'expected' => null,
            ],
            'hasNextPage is false and createAt is null case' => [
                'hasNextPage' => false,
                'nextPage' => null,
                'isEmpty' => false,
                'dataList' => collect([
                    [
                        'id' => 'id-xx1',
                        'created_at' => '2021-12-01'
                    ],
                    [
                        'id' => 'id-xx2',
                        'created_at' => '2021-12-02'
                    ]
                ]),
                'createdAt' => null,
                'expected' => collect([
                    [
                        'id' => 'id-xx1',
                        'created_at' => '2021-12-01'
                    ],
                    [
                        'id' => 'id-xx2',
                        'created_at' => '2021-12-02'
                    ]
                ]),
            ],
            'hasNextPage is true and createAt is null case' => [
                'hasNextPage' => false,
                'nextPage' => null,
                'isEmpty' => false,
                'dataList' => collect([
                    [
                        'id' => 'id-xx1',
                        'created_at' => '2021-12-01'
                    ],
                    [
                        'id' => 'id-xx2',
                        'created_at' => '2021-12-02'
                    ]
                ]),
                'createdAt' => null,
                'expected' => collect([
                    [
                        'id' => 'id-xx1',
                        'created_at' => '2021-12-01'
                    ],
                    [
                        'id' => 'id-xx2',
                        'created_at' => '2021-12-02'
                    ]
                ]),
            ],
            'hasNextPage is false and not slice case' => [
                'hasNextPage' => false,
                'nextPage' => null,
                'isEmpty' => false,
                'dataList' => collect([
                    [
                        'id' => 'id-xx1',
                        'created_at' => '2021-12-01'
                    ],
                    [
                        'id' => 'id-xx2',
                        'created_at' => '2021-12-02'
                    ]
                ]),
                'createdAt' => new Colmun('created_at', '2021-01-01'),
                'expected' => collect([
                    [
                        'id' => 'id-xx1',
                        'created_at' => '2021-12-01'
                    ],
                    [
                        'id' => 'id-xx2',
                        'created_at' => '2021-12-02'
                    ]
                ]),
            ],
            'hasNextPage is ture and slice case' => [
                'hasNextPage' => true,
                'nextPage' => 'next',
                'isEmpty' => false,
                'dataList' => collect([
                    [
                        'id' => 'id-xx1',
                        'created_at' => '2021-12-01'
                    ],
                    [
                        'id' => 'id-xx2',
                        'created_at' => '2021-12-02'
                    ]
                ]),
                'createdAt' => new Colmun('created_at', '2021-12-01'),
                'expected' => collect([
                    [
                        'id' => 'id-xx2',
                        'created_at' => '2021-12-02'
                    ]
                ]),
            ],
        ];
    }
}
