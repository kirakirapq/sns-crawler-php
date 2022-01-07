<?php

namespace Unit\Entities\BigQuery;

use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\LatestData;
use Tests\TestCase;

class LatestDataTest extends TestCase
{
    /**
     * testGetName
     * @dataProvider nameProvider
     *
     * @return void
     */
    public function testGetName(string $tableName, array $value, string $expected): void
    {
        $model = new LatestData($tableName, $value);

        $this->assertEquals($expected, $model->getName());
    }

    public function nameProvider(): array
    {
        return [
            'int' => [
                'tableName' => 'tb1',
                'value' => [],
                'expected' => 'tb1',
            ],
        ];
    }

    /**
     * getColmuns
     * @test
     * @dataProvider colmunsProvider
     *
     * @return void
     */
    public function getColmuns(string $tableName, array $value, array $expected): void
    {
        $model = new LatestData($tableName, $value);

        $this->assertEquals($expected, $model->getColmuns());
    }

    public function colmunsProvider(): array
    {
        return [
            'int' => [
                'tableName' => 'tb1',
                'value' => ['f1' => new Colmun('f1', 'value')],
                'expected' => ['f1' => new Colmun('f1', 'value')],
            ],
        ];
    }

    /**
     * getColmun
     * @test
     * @dataProvider colmunProvider
     *
     * @return void
     */
    public function getColmun(string $tableName, string $field, array $value, Colmun $expected): void
    {
        $model = new LatestData($tableName, $value);

        $this->assertEquals($expected, $model->getColmun($field));
    }

    public function colmunProvider(): array
    {
        $col = new Colmun('f1', 'value');
        return [
            'null case' => [
                'tableName' => 'tbl',
                'field' => 'f1',
                'value' => ['f1' => $col],
                'expected' => $col,
            ],
        ];
    }
}
