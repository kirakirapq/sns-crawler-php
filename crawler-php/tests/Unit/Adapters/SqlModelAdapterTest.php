<?php

namespace Unit\Adapters;

use App\Adapters\SqlModelAdapter;
use App\Application\InputData\BigQueryRiskWordSql;
use App\Application\InputData\LatestCommentSql;
use App\Application\InputData\RiskWordListSql;
use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\ColmunType;
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
        $targetField = '';
        $createdAt = new Colmun('dt', '2021-01-01', ColmunType::DATE);

        $expected = new BigQueryRiskWordSql(
            $projectId,
            $datasetId,
            $tableId,
            $riskManageTable,
            $riskWords,
            $appName,
            $language,
            $targetField,
            $createdAt
        );

        $actual = SqlModelAdapter::getBigQueryRiskWordSql(
            $projectId,
            $datasetId,
            $tableId,
            $riskManageTable,
            $riskWords,
            $appName,
            $language,
            $targetField,
            $createdAt
        );

        $this->assertInstanceOf(BigQueryRiskWordSql::class, $actual);

        $this->assertEquals($expected, $actual);
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

        $expected = new LatestCommentSql(
            $projectId,
            $datasetId,
            $tableId
        );

        $actual = SqlModelAdapter::getLatestCommentSql(
            $projectId,
            $datasetId,
            $tableId,
        );

        $this->assertInstanceOf(LatestCommentSql::class, $actual);
        $this->assertEquals($expected, $actual);
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

        $expected = new RiskWordListSql(
            $projectId,
            $datasetId
        );

        $actual = SqlModelAdapter::getRiskWordListSql(
            $projectId,
            $datasetId
        );

        $this->assertInstanceOf(RiskWordListSql::class, $actual);
        $this->assertEquals($expected, $actual);
    }
}
