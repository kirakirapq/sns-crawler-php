<?php

namespace Unit\Adapters;

use App\Adapters\BigQueryResponseAdapter;
use App\Application\InputData\BigQuerySqlModel;
use App\Application\InputData\BigQueryRiskWordSql;
use App\Application\Interactors\BigQuery\BigQueryManager;
use App\Application\OutputData\InnerApiResponse\BigQueryResponse;
// use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Application\Repositories\BigQuery\BigQueryRepository;
use App\Entities\ResponseData\Bigquery\BigQueryData;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use \Mockery;

class BigQueryManagerTest extends TestCase
{
    /**
     * existsTable
     * @test
     * @dataProvider existsTableData
     *
     * @param  mixed $expected
     * @return void
     */
    public function existsTable(bool $expected): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn('twitter', 'dataset', 'table')
            ->times(3);

        $bigQueryRepository = Mockery::mock(BigQueryRepository::class);
        $bigQueryRepository->shouldReceive('existsTable')->andReturn($expected)->once();


        $manager = new BigQueryManager($bigQueryRepository);
        $actual = $manager->existsTable('twitter', 'en');

        $this->assertEquals($expected, $actual);
    }

    public function existsTableData(): array
    {
        return [
            'true case' => [
                'expected' => true,
            ],
            'false case' => [
                'expected' => false,
            ]
        ];
    }

    /**
     * getData
     * @test
     * @dataProvider getDataProvider
     *
     * @return void
     */
    public function getData($hasError, $getBodyCount, $getBigqueryDataCount): void
    {
        $sqlModel = Mockery::mock(BigQuerySqlModel::class);

        $bigQueryResponse = Mockery::mock(BigQueryResponse::class);
        $bigQueryResponse->shouldReceive('hasError')->andReturn($hasError)->once();
        $bigQueryResponse->shouldReceive('getBody')->times($getBodyCount);

        $bigQueryRepository = Mockery::mock(BigQueryRepository::class);
        $bigQueryRepository->shouldReceive('getData')->andReturn($bigQueryResponse)->once();

        Mockery::mock('alias:' . BigQueryResponseAdapter::class)
            ->shouldReceive('getBigqueryData')
            ->andReturn(Mockery::mock(BigQueryData::class))
            ->times($getBigqueryDataCount);

        if ($hasError === true) {
            Log::shouldReceive('error')->once();
        }

        $manager = new BigQueryManager($bigQueryRepository);
        $actual = $manager->getData($sqlModel);

        if ($hasError === true) {
            $this->assertNull($actual);
        } else {

            $this->assertInstanceOf(BigQueryData::class, $actual);
        }
    }

    public function getDataProvider(): array
    {
        return [
            'has error case' => [
                'hasError' => true,
                'getBodyCount' => 1,
                'getBigqueryDataCount' => 0,
            ],
            'has not error case' => [
                'hasError' => false,
                'getBodyCount' => 0,
                'getBigqueryDataCount' => 1,
            ]
        ];
    }

    /**
     * loadBigQuery
     * @test
     * @dataProvider loadBigQueryData
     *
     * @return void
     */
    public function loadBigQuery($hasError): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn('twitter', 'dataset', 'table')
            ->times(3);

        $apiResponse = Mockery::mock(BigQueryResponse::class)
            ->shouldReceive(
                [
                    'hasError' => $hasError,
                    'getBody' => ''
                ]
            )->getMock();

        $bigQueryRepository = Mockery::mock(BigQueryRepository::class);
        $bigQueryRepository->shouldReceive('loadBigQuery')->andReturn($apiResponse)->once();

        if ($hasError === true) {
            Log::shouldReceive('error')->once();
        }


        $manager = new BigQueryManager($bigQueryRepository);
        $manager->loadBigQuery('twitter', 'en', 'filename.csv');

        $this->assertEquals(1, 1);
    }

    public function loadBigQueryData(): array
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
     * insertBigQuery
     * @test
     * @dataProvider insertBigQueryData
     *
     * @param  mixed $hasError
     * @return void
     */
    public function insertBigQuery($hasError): void
    {
        $apiResponse = Mockery::mock(BigQueryResponse::class)
            ->shouldReceive(
                [
                    'hasError' => $hasError,
                    'getBody' => ''
                ]
            )->getMock();

        $bigQueryRepository = Mockery::mock(BigQueryRepository::class);
        $bigQueryRepository->shouldReceive('insertBigQuery')->andReturn($apiResponse)->once();

        $sqlModel = new BigQueryRiskWordSql('prj', 'db', 'tbl1', 'tbl2', collect([]), 'app', 'lung');

        if ($hasError === true) {
            Log::shouldReceive('error')->once();
        }

        $manager = new BigQueryManager($bigQueryRepository);
        $actual = $manager->insertBigQuery('db', 'tbl1', 'tbl2', $sqlModel);

        $this->assertEquals($apiResponse, $actual);
    }

    public function insertBigQueryData(): array
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
     * getProjectId
     * @test
     *
     * @return void
     */
    public function getProjectId(): void
    {
        $expected = 'prj';
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($expected)
            ->times(1);

        $bigQueryRepository = Mockery::mock(BigQueryRepository::class);
        $manager = new BigQueryManager($bigQueryRepository);

        $this->assertEquals($expected, $manager->getProjectId());
    }

    /**
     * getDatasetId
     * @test
     * @dataProvider getDatasetIdDataProvider
     *
     * @return void
     */
    public function getDatasetId($appName, $expected): void
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($expected)
            ->times(1);

        $bigQueryRepository = Mockery::mock(BigQueryRepository::class);
        $manager = new BigQueryManager($bigQueryRepository);

        $this->assertEquals($expected, $manager->getDatasetId($appName));
    }

    public function getDatasetIdDataProvider(): array
    {
        return [
            'twiiter case' => [
                'appName' => 'twitter',
                'expected' => 'wwo_test',
            ],
            'other case' => [
                'appName' => 'other',
                'expected' => '',
            ]
        ];
    }

    /**
     * getTableId
     * @test
     *
     * @return void
     */
    public function getTableId(): void
    {
        $expected = 'tbl1';

        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($expected)
            ->times(1);

        $bigQueryRepository = Mockery::mock(BigQueryRepository::class);
        $manager = new BigQueryManager($bigQueryRepository);

        $this->assertEquals($expected, $manager->getTableId('wwo_test', 'twitter', 'en'));
    }

    /**
     * getRiskCommentTableId
     * @test
     *
     * @return void
     */
    public function getRiskCommentTableId(): void
    {
        $expected = 'tbl12';
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->andReturn($expected)
            ->times(1);

        $bigQueryRepository = Mockery::mock(BigQueryRepository::class);
        $manager = new BigQueryManager($bigQueryRepository);

        $this->assertEquals($expected, $manager->getRiskCommentTableId('wwo_test', 'twitter', 'en'));
    }
}
