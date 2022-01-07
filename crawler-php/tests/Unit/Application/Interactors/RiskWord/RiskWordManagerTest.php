<?php

namespace Unit\Application\interractors;

use App\Adapters\SqlModelAdapter;
use App\Application\InputData\BigQuerySqlModel;
use App\Application\Interactors\RiskWord\RiskWordManager;
use App\Application\OutputData\InnerApiResponse\BigQueryResponse;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Entities\ResponseData\Bigquery\BigQueryData;
use App\Entities\RiskWord\RiskCommentList;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class RiskWordManagerTest extends TestCase
{
    /**
     * getRiskWordList
     * @test
     * @dataProvider getRiskWordListDataProvider
     *
     * @param  mixed $hasError
     * @return void
     */
    public function getRiskWordList($hasError): void
    {
        $response = Mockery::mock(BigQueryData::class)
            ->shouldReceive(
                [
                    'hasError' => $hasError,
                ]

            )->once()
            ->getMock();

        if ($hasError === true) {
            $response->shouldReceive('getErrorMessage')->once();
            Log::shouldReceive('error')->once();
        }

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive(['get' => ''])
            ->with('crawl.name')
            ->once();

        $bigQueryUseCase = Mockery::mock(BigQueryUseCase::class)
            ->shouldReceive(['getProjectId' => ''])
            ->once();
        $bigQueryUseCase->shouldReceive(['getDatasetId' => ''])->once();
        $bigQueryUseCase->shouldReceive(['getData' => $response])
            ->once();

        $manager = new RiskWordManager($bigQueryUseCase->getMock());
        $actual = $manager->getRiskWordList();

        $this->assertEquals($response, $actual);
    }

    public function getRiskWordListDataProvider(): array
    {
        return [
            'error case' => [
                'hasError' => true,
            ],
            'not error case' => [
                'hasError' => false,
            ],
        ];
    }

    /**
     * getRiskComment
     * @test
     * @dataProvider getRiskCommentDataProvider
     *
     * @param  mixed $dataList
     * @param  mixed $statusCode
     * @param  mixed $errorMessage
     * @param  mixed $hasError
     * @param  mixed $expected
     * @return void
     */
    public function getRiskComment($dataList, $statusCode, $errorMessage, $expected): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive(['get' => 'twitter'])
            ->with('crawl.name')
            ->atMost()
            ->times(1);

        $riskWordResponse = Mockery::mock(BigQueryData::class)
            ->shouldReceive(
                [
                    'getStatusCode' => $statusCode,
                    'getDataList' => collect($dataList),
                    'getErrorMessage' => $errorMessage,
                ]
            )
            ->once()
            ->getMock();

        $bigQueryUseCase = Mockery::mock(BigQueryUseCase::class);
        $bigQueryUseCase->shouldReceive([
            'getProjectId' => '',
            'getDatasetId' => '',
            'getTableId' => '',
            'getRiskCommentTableId' => '',
            'getData' => $riskWordResponse
        ]);

        $manager = new RiskWordManager($bigQueryUseCase);
        $actual = $manager->getRiskComment('title', 'lung');

        $this->assertEquals($expected, $actual);
    }

    public function getRiskCommentDataProvider(): array
    {
        return [
            'success case' => [
                'datalist' => [
                    [
                        'app_name' => 'kms',
                        'language' => 'en',
                        'id' => 'idxxx',
                        'text' => '',
                        'translated' => '',
                    ]
                ],
                'statusCode' => 200,
                'errorMessage' => 'no message',
                'expected' => (new RiskCommentList(
                    200,
                    collect([
                        [
                            'app_name' => 'kms',
                            'language' => 'en',
                            'id' => 'idxxx',
                            'text' => '',
                            'translated' => '',
                        ]
                    ]),
                    'no message'
                ))
            ],
            'error case' =>
            [
                'datalist' => [
                    []
                ],
                'statusCode' => 500,
                'errorMessage' => 'server error.',
                'expected' => (new RiskCommentList(
                    500,
                    collect([]),
                    'server error.'
                ))
            ],
        ];
    }

    /**
     * loadRiskComment
     * @test
     * @dataProvider loadRiskWordCommentDataProvider
     *
     *
     * @param  mixed $hasError
     * @param  mixed $expected
     * @return void
     */
    public function loadRiskComment($hasError, $expected): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive(['get' => ''])
            ->with('crawl.name')
            ->andReturn('')
            ->atMost()
            ->times(2);

        $riskWordResponse = Mockery::mock(BigQueryData::class)
            ->shouldReceive(
                [
                    'getDataList' => collect([]),
                    'hasError' => false,
                ]

            )->atMost()
            ->times(1)
            ->getMock();

        $insertResponse = Mockery::mock(BigQueryResponse::class)
            ->shouldReceive(
                [
                    'hasError' => $hasError,
                ]

            )
            ->atMost()
            ->times(1)
            ->getMock();

        if ($hasError === true) {
            $insertResponse->shouldReceive('getBody')->once();
            Log::shouldReceive('error')->once();
        }

        $bigQueryUseCase = Mockery::mock(BigQueryUseCase::class);
        $bigQueryUseCase->shouldReceive([
            'getProjectId' => '',
            'getDatasetId' => '',
            'getTableId' => '',
            'getRiskCommentTableId' => '',
            'insertBigQuery' => $insertResponse,
            'getData' => $riskWordResponse
        ]);

        $manager = new RiskWordManager($bigQueryUseCase);
        $actual = $manager->loadRiskComment('twitter', 'title', 'lung');

        $this->assertEquals($expected, $actual);
    }

    public function loadRiskWordCommentDataProvider(): array
    {
        return [
            'error case' => [
                'hasError' => true,
                'expected' => false,
            ],
            'not error case' => [
                'hasError' => false,
                'expected' => true,
            ],
        ];
    }
}
