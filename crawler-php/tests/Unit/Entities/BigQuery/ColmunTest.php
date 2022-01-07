<?php

namespace Unit\Entities\BigQuery;

use App\Entities\BigQuery\Colmun;
use App\Exceptions\ObjectDefinitionErrorException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ColmunTest extends TestCase
{
    /**
     * testGetName
     * @dataProvider nameProvider
     *
     * @return void
     */
    public function testGetName(string $fieldName, int|array|string $value, string $expected): void
    {
        $model = new Colmun($fieldName, $value);

        $this->assertEquals($expected, $model->getName());
    }

    public function nameProvider(): array
    {
        return [
            'int' => [
                'fieldName' => 'field1',
                'value' => 123,
                'expected' => 'field1',
            ],
            'string' => [
                'fieldName' => 'field1',
                'value' => 'string',
                'expected' => 'field1',
            ],
            'array' => [
                'fieldName' => 'field1',
                'value' => [1, 2, 3],
                'expected' => 'field1',
            ]
        ];
    }

    /**
     * getValue
     * @test
     * @dataProvider valueProvider
     *
     * @return void
     */
    public function getValue(string $fieldName, int|array|string $value, int|array|string $expected): void
    {
        $model = new Colmun($fieldName, $value);

        $this->assertEquals($expected, $model->getValue());
    }

    public function valueProvider(): array
    {
        return [
            'int' => [
                'fieldName' => 'field1',
                'value' => 123,
                'expected' => 123,
            ],
            'string' => [
                'fieldName' => 'field1',
                'value' => 'string',
                'expected' => 'string',
            ],
            'array' => [
                'fieldName' => 'field1',
                'value' => [1, 2, 3],
                'expected' => [1, 2, 3],
            ]
        ];
    }

    /**
     * getType
     * @test
     * @dataProvider typeProvider
     *
     * @return void
     */
    public function getType(bool $isError, ?string $type, ?string $expected): void
    {
        if ($isError === true) {
            Log::shouldReceive('error');
            $this->expectException(ObjectDefinitionErrorException::class);
            $this->expectExceptionCode(500);
        }

        $model = new Colmun('f1', 'v1', $type);

        if ($isError === false) {
            $this->assertEquals($expected, $model->getType());
        }
    }

    public function typeProvider(): array
    {
        return [
            'null case' => [
                'isError' => false,
                'type' => null,
                'expected' => null,
            ],
            'success case' => [
                'isError' => false,
                'type' => 'STRING',
                'expected' => 'STRING',
            ],
            'error case' => [
                'isError' => true,
                'type' => 'hoge',
                'expected' => null,
            ]
        ];
    }
}
