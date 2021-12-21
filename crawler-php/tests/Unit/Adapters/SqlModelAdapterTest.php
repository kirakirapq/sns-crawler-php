<?php

namespace Unit\Adapters;

use App\Adapters\SqlModelAdapter;
use App\Application\InputData\BigQueryRiskWordSql;
use App\Application\InputData\LatestCommentSql;
use App\Application\InputData\RiskWordListSql;
use Tests\TestCase;
use \Mockery;

class SqlModelAdapterTest extends TestCase
{
    /**
     * getBigQueryRiskWordSql
     * @test
     *
     * @return void
     */
    public function getBigQueryRiskWordSql(): void
    {
        $projectId = '';
        $datasetId = '';
        $tableId = '';
        $riskManageTable = '';
        $riskWords = collect([]);
        $appName = '';
        $language = '';
        $createdAt = null;

        $response = new BigQueryRiskWordSql(
            $projectId,
            $datasetId,
            $tableId,
            $riskManageTable,
            $riskWords,
            $appName,
            $language,
            $createdAt
        );

        $adapter = Mockery::mock('alias:' . SqlModelAdapter::class);
        $adapter->shouldReceive('getBigQueryRiskWordSql')->andReturn($response);

        $actual = SqlModelAdapter::getBigQueryRiskWordSql(
            $projectId,
            $datasetId,
            $tableId,
            $riskManageTable,
            $riskWords,
            $appName,
            $language,
            $createdAt
        );

        $this->assertInstanceOf(BigQueryRiskWordSql::class, $actual);
    }

    /**
     * getLatestCommentSql
     * @test
     *
     * @return void
     */
    public function getLatestCommentSql(): void
    {
        $projectId = '';
        $datasetId = '';
        $tableId = '';

        $response = new LatestCommentSql(
            $projectId,
            $datasetId,
            $tableId
        );

        $adapter = Mockery::mock('alias:' . SqlModelAdapter::class);
        $adapter->shouldReceive('getLatestCommentSql')->andReturn($response);

        $actual = SqlModelAdapter::getLatestCommentSql(
            $projectId,
            $datasetId,
            $tableId,
        );

        $this->assertInstanceOf(LatestCommentSql::class, $actual);
    }

    /**
     * getRiskWordListSql
     * @test
     *
     * @return void
     */
    public function getRiskWordListSql(): void
    {
        $projectId = '';
        $datasetId = '';

        $response = new RiskWordListSql(
            $projectId,
            $datasetId
        );

        $adapter = Mockery::mock('alias:' . SqlModelAdapter::class);
        $adapter->shouldReceive('getRiskWordListSql')->andReturn($response);

        $actual = SqlModelAdapter::getRiskWordListSql(
            $projectId,
            $datasetId
        );

        $this->assertInstanceOf(RiskWordListSql::class, $actual);
    }
}
