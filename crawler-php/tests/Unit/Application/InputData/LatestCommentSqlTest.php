<?php

namespace Unit\Application\InputData;

use App\Application\InputData\LatestCommentSql;
use Tests\TestCase;

class LatestCommentSqlTest extends TestCase
{
    /**
     * getSql
     * @test
     * @dataProvider getSqlData
     *
     * @return void
     */
    public function getSql(string $projectId, string $datasetId, string $tableId, string $expected): void
    {
        $model = new LatestCommentSql(
            $projectId,
            $datasetId,
            $tableId
        );

        $this->assertTrue(0 < strpos($model->getSql(), $expected));
    }

    public function getSqlData(): array
    {
        return [
            'reskwords count 0 case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'tableId' => 'tbl',
                'expected' => 'prj.dt.tbl',
            ],
        ];
    }

    /**
     * getParameters
     * @test
     *
     * @return void
     */
    public function getParameters(): void
    {
        $model = new LatestCommentSql('', '', '');
        $this->assertEquals([], $model->getParameters());
    }

    /**
     * hasParameters
     * @test
     *
     * @return void
     */
    public function hasParameters(): void
    {
        $model = new LatestCommentSql('', '', '');
        $this->assertEquals(false, $model->hasParameters());
    }
}
