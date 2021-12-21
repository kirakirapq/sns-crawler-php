<?php

namespace Unit\Application\InputData;

use App\Adapters\TargetDateAdapter;
use App\Application\InputData\BigQueryRiskWordSql;
use App\Application\InputData\TargetDate;
use Illuminate\Support\Collection;
use Tests\TestCase;
use \Mockery;

class BigQueryRiskWordSqlTest extends TestCase
{
    /**
     * getSql
     * @test
     * @dataProvider getSqlData
     *
     * @return void
     */
    public function getSql(Collection $riskwords, $createdAt, array $expected): void
    {
        $projectId = '';
        $datasetId = '';
        $tableId   = '';
        $riskManageTable = '';
        $appName = '';
        $language = '';

        $model = new BigQueryRiskWordSql(
            $projectId,
            $datasetId,
            $tableId,
            $riskManageTable,
            $riskwords,
            $appName,
            $language,
            $createdAt
        );

        if (empty($expected['riskClause']) === true) {
            $this->assertTrue(strpos($model->getSql(), 'CONTAINS_SUBSTR') === false);
        } else {
            $this->assertTrue(0 < strpos($model->getSql(), $expected['riskClause']));
        }

        if (empty($expected['createdAtClause']) === true) {
            $this->assertTrue(strpos($model->getSql(), 'AND date >= ?') === false);
        } else {
            $this->assertTrue(0 < strpos($model->getSql(), $expected['createdAtClause']));
        }
    }

    public function getSqlData(): array
    {
        return [
            'reskwords count 0 case' => [
                'riskwords' => collect([]),
                'createdAt' => null,
                'expected' => [
                    'riskClause' => '',
                    'createdAtClause' => ''
                ],
            ],
            'reskwords count 1 case' => [
                'riskwords' => collect([[
                    'word' => 'test'
                ]]),
                'createdAt' => null,
                'expected' => [
                    'riskClause' => 'CONTAINS_SUBSTR((text, translated), ?)',
                    'createdAtClause' => ''
                ],
            ],
            'reskwords count over than 1 case' => [
                'riskwords' => collect([
                    ['word' => 'test'],
                    ['word' => 'test']
                ]),
                'createdAt' => null,
                'expected' => [
                    'riskClause' => 'CONTAINS_SUBSTR((text, translated), ?) OR CONTAINS_SUBSTR((text, translated), ?)',
                    'createdAtClause' => ''
                ],
            ],
            'createdAt is null case' => [
                'riskwords' => collect([]),
                'createdAt' => null,
                'expected' => [
                    'riskClause' => '',
                    'createdAtClause' => ''
                ],
            ],
            'createdAt is not null case' => [
                'riskwords' => collect([]),
                'createdAt' => '2021-01-01',
                'expected' => [
                    'riskClause' => '',
                    'createdAtClause' => 'AND date >= ?'
                ],
            ],
        ];
    }

    /**
     * getParameters
     * @test
     * @dataProvider getParametersData
     *
     * @param  mixed $riskwords
     * @param  mixed $createdAt
     * @param  mixed $expected
     * @return void
     */
    public function getParameters(Collection $riskwords, $createdAt, array $expected): void
    {
        $projectId = '';
        $datasetId = '';
        $tableId   = '';
        $riskManageTable = '';
        $appName = '';
        $language = '';

        $model = new BigQueryRiskWordSql(
            $projectId,
            $datasetId,
            $tableId,
            $riskManageTable,
            $riskwords,
            $appName,
            $language,
            $createdAt
        );

        $this->assertEquals($expected, $model->getParameters());
    }

    public function getParametersData(): array
    {
        return [
            'riskword count 0 and createdAt null case' => [
                'reskword' => collect([]),
                'createdAt' => null,
                'expected' => [],

            ],
            'riskword count 0 and createdAt not null case' => [
                'reskword' => collect([]),
                'createdAt' => '2021-01-01',
                'expected' => [
                    '2021-01-01',
                ],

            ],
            'riskword count 1  and createdAt null case' => [
                'reskword' => collect([
                    ['word' => 'test']
                ]),
                'createdAt' => null,
                'expected' => [
                    'test'
                ],
            ],
            'riskword count 1  and createdAt not null case' => [
                'reskword' => collect([
                    ['word' => 'test']
                ]),
                'createdAt' => '2021-01-01',
                'expected' => [
                    'test',
                    '2021-01-01',
                ],
            ],
        ];
    }

    /**
     * hasParameters
     * @test
     * @dataProvider hasParametersData
     *
     * @param  mixed $riskwords
     * @param  mixed $createdAt
     * @param  bool $expected
     * @return void
     */
    public function hasParameters(Collection $riskwords, $createdAt, bool $expected): void
    {
        $projectId = '';
        $datasetId = '';
        $tableId   = '';
        $riskManageTable = '';
        $appName = '';
        $language = '';

        $model = new BigQueryRiskWordSql(
            $projectId,
            $datasetId,
            $tableId,
            $riskManageTable,
            $riskwords,
            $appName,
            $language,
            $createdAt
        );

        $this->assertEquals($expected, $model->hasParameters());
    }

    public function hasParametersData(): array
    {
        return [
            'riskword count 0 and createdAt null case' => [
                'reskword' => collect([]),
                'createdAt' => null,
                'expected' => false,

            ],
            'riskword count 0 and createdAt not null case' => [
                'reskword' => collect([]),
                'createdAt' => '2021-01-01',
                'expected' => true,

            ],
            'riskword count 1  and createdAt null case' => [
                'reskword' => collect([
                    ['word' => 'test']
                ]),
                'createdAt' => null,
                'expected' => true,
            ],
            'riskword count 1  and createdAt not null case' => [
                'reskword' => collect([
                    ['word' => 'test']
                ]),
                'createdAt' => '2021-01-01',
                'expected' => true,
            ],
        ];
    }
}
