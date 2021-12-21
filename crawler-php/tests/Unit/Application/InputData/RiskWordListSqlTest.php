<?php

namespace Unit\Application\InputData;

use App\Application\InputData\RiskWordListSql;
use Tests\TestCase;

class RiskWordListSqlTest extends TestCase
{
    /**
     * getSql
     * @test
     * @dataProvider getSqlData
     *
     * @return void
     */
    public function getSql(string $projectId, string $datasetId, string $expected): void
    {
        $model = new RiskWordListSql(
            $projectId,
            $datasetId
        );

        $this->assertTrue(0 < strpos($model->getSql(), $expected));
    }

    public function getSqlData(): array
    {
        return [
            'reskwords count 0 case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'expected' => 'prj.dt',
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
        $model = new RiskWordListSql('', '', '');
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
        $model = new RiskWordListSql('', '', '');
        $this->assertEquals(false, $model->hasParameters());
    }
}
