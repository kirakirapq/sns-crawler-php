<?php

namespace Unit\Application\InputData;

use App\Application\InputData\RiskCommentListSql;
use Tests\TestCase;

class RiskCommentListSqlTest extends TestCase
{
    /**
     * getSql
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @test
     * @dataProvider getSqlDataProvider
     *
     * @return void
     */
    public function getSql($projectId, $datasetId, $tableId, $title, $language, $createdAt, $expected): void
    {
        $model = new RiskCommentListSql(
            $projectId,
            $datasetId,
            $tableId,
            $title,
            $language,
            $createdAt
        );

        $actual = $model->getSql();

        if ($expected['table'] === true) {
            $this->assertTrue(0 < strpos($actual, 'prj.dt.tbl'));
        } else {
            $this->assertFalse(strpos($actual, 'prj.dt.tbl'));
        }

        if ($expected['dt'] === true) {
            $this->assertTrue(0 < strpos($actual, 'date >= ?'));
        } else {
            $this->assertFalse(strpos($actual, 'date >= ?'));
        }

        if ($expected['created_at'] === true) {
            $this->assertTrue(0 < strpos(
                $actual,
                'FORMAT_TIMESTAMP(\'%Y-%m-%d %H:%M:%S\',created_at,\'Asia/Tokyo\') > ?'
            ));
        } else {
            $this->assertFalse(strpos(
                $actual,
                'FORMAT_TIMESTAMP(\'%Y-%m-%d %H:%M:%S\',created_at,\'Asia/Tokyo\') > ?'
            ));
        }

        if ($expected['app_name'] === true) {
            $this->assertTrue(0 < strpos($actual, 'app_name = ?'));
        } else {
            $this->assertFalse(strpos($actual, 'app_name = ?'));
        }

        if ($expected['language'] === true) {
            $this->assertTrue(0 < strpos($actual, 'language = ?'));
        } else {
            $this->assertFalse(strpos($actual, 'language = ?'));
        }
    }

    public function getSqlDataProvider(): array
    {
        return [
            'normal case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => 'kms',
                'language' => 'en',
                'createdAt' => '2021-10-10 00:00:00',
                'expected' => [
                    'table' => true,
                    'dt' => true,
                    'created_at' => true,
                    'app_name' => true,
                    'language' => true,
                ],
            ],
            'title null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => null,
                'language' => 'en',
                'createdAt' => '2021-10-10 00:00:00',
                'expected' => [
                    'table' => true,
                    'dt' => true,
                    'created_at' => true,
                    'app_name' => false,
                    'language' => true,
                ],
            ],
            'language null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => 'kms',
                'language' => null,
                'createdAt' => '2021-10-10 00:00:00',
                'expected' => [
                    'table' => true,
                    'dt' => true,
                    'created_at' => true,
                    'app_name' => true,
                    'language' => false,
                ],
            ],
            'created_at null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => 'kms',
                'language' => 'en',
                'createdAt' => null,
                'expected' => [
                    'table' => true,
                    'dt' => false,
                    'created_at' => false,
                    'app_name' => true,
                    'language' => true,
                ],
            ],
            'all null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => null,
                'language' => null,
                'createdAt' => null,
                'expected' => [
                    'table' => true,
                    'dt' => false,
                    'created_at' => false,
                    'app_name' => false,
                    'language' => false,
                ],
            ],
        ];
    }

    /**
     * getParameters
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @test
     * @dataProvider getParametersDataProvider
     *
     * @return void
     */
    public function getParameters($projectId, $datasetId, $tableId, $title, $language, $createdAt, $expected): void
    {
        $model = new RiskCommentListSql(
            $projectId,
            $datasetId,
            $tableId,
            $title,
            $language,
            $createdAt
        );

        $this->assertEquals($expected, $model->getParameters());
    }

    public function getParametersDataProvider(): array
    {
        return [
            'normal case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => 'kms',
                'language' => 'en',
                'createdAt' => '2021-10-10 00:00:00',
                'expected' => [
                    '2021-10-10',
                    '2021-10-10 09:00:00',
                    'kms',
                    'en'
                ],
            ],
            'title null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => null,
                'language' => 'en',
                'createdAt' => '2021-10-10 00:00:00',
                'expected' => [
                    '2021-10-10',
                    '2021-10-10 09:00:00',
                    'en'
                ],
            ],
            'language null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => 'kms',
                'language' => null,
                'createdAt' => '2021-10-10 00:00:00',
                'expected' => [
                    '2021-10-10',
                    '2021-10-10 09:00:00',
                    'kms',
                ],
            ],
            'created_at null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => 'kms',
                'language' => 'en',
                'createdAt' => null,
                'expected' => [
                    'kms',
                    'en'
                ],
            ],
            'all null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => null,
                'language' => null,
                'createdAt' => null,
                'expected' => [],
            ],
        ];
    }

    /**
     * hasParameters
     * @
     * @dataProvider hasParametersDataProvider
     *
     * @return void
     */
    public function hasParameters($projectId, $datasetId, $tableId, $title, $language, $createdAt, $expected): void
    {
        $model = new RiskCommentListSql(
            $projectId,
            $datasetId,
            $tableId,
            $title,
            $language,
            $createdAt
        );

        $this->assertEquals($expected, $model->hasParameters());
    }

    public function hasParametersDataProvider(): array
    {
        return [
            'normal case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => 'kms',
                'language' => 'en',
                'createdAt' => '2021-10-10 00:00:00',
                'expected' => true,
            ],
            'title null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => null,
                'language' => 'en',
                'createdAt' => '2021-10-10 00:00:00',
                'expected' => true,
            ],
            'language null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => 'kms',
                'language' => null,
                'createdAt' => '2021-10-10 00:00:00',
                'expected' => true,
            ],
            'created_at null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => null,
                'language' => 'en',
                'createdAt' => null,
                'expected' => true
            ],
            'all null case' => [
                'projectId' => 'prj',
                'datasetId' => 'dt',
                'table' => 'tbl',
                'title' => null,
                'language' => null,
                'createdAt' => null,
                'expected' => false,
            ],
        ];
    }
}
