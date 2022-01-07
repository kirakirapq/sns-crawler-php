<?php

namespace Unit\Application\OutputData\InnerApiResponse;

use App\Application\OutputData\InnerApiResponse\BigQueryResponse;
use \ArrayIterator;
use Tests\TestCase;

class BigQueryResponseTest extends TestCase
{
    /**
     * getStatusCode
     * @test
     *
     * @return void
     */
    public function getStatusCode(): void
    {
        $model = new BigQueryResponse(200, '');

        $this->assertEquals(200, $model->getStatusCode());
    }

    /**
     * getBody
     * @test
     * @dataProvider bodyProvider
     *
     * @return void
     */
    public function getBody($body): void
    {
        $model = new BigQueryResponse(200, $body);

        $this->assertEquals($body, $model->getBody());
    }

    public function bodyProvider(): array
    {
        return [
            'array' => [
                'body' => ['', ''],
            ],
            'bool' => [
                'body' => false,
            ],
            'int' => [
                'body' => 1,
            ],
            'float' => [
                'body' => 1.0,
            ],
            'string' => [
                'body' => 'string',
            ],
            'object' => [
                'body' => new class
                {
                    public function rows()
                    {
                        return '';
                    }
                },
            ],
        ];
    }

    /**
     * getBodyAsArray
     * @test
     * @dataProvider getBodyAsArrayProvider
     *
     * @param  mixed $isInstance
     * @param  mixed $body
     * @param  mixed $expected
     * @return void
     */
    public function getBodyAsArray(mixed $body, array $expected): void
    {
        $model = new BigQueryResponse(200, $body);

        $this->assertEquals($expected, $model->getBodyAsArray());
    }

    public function getBodyAsArrayProvider(): array
    {
        $expected = [1, 2, 3];

        $ins = new class()
        {
            public function rows()
            {
                return new ArrayIterator([1, 2, 3]);
            }
        };

        return [
            'iterator case' => [
                'body' => $ins,
                'expected' => $expected,
            ],
            'array case' => [
                'body' => $expected,
                'expected' => $expected,
            ],
            'other case' => [
                'body' => '',
                'expected' => [],
            ]
        ];
    }

    /**
     * getBody
     * @test
     * @dataProvider hasErrorProvider
     *
     * @return void
     */
    public function hasError(int $code, bool $expected): void
    {
        $model = new BigQueryResponse($code, []);

        $this->assertEquals($expected, $model->hasError());
    }

    public function hasErrorProvider(): array
    {
        return [
            'not error case 1' => [
                'code' => 200,
                'expected' => false,
            ],
            'not error case 2' => [
                'code' => 201,
                'expected' => false,
            ],
            'not error case 3' => [
                'code' => 209,
                'expected' => false,
            ],
            'error case 1' => [
                'code' => 300,
                'expected' => true,
            ],
            'error case 2' => [
                'code' => 400,
                'expected' => true,
            ],
            'error case 3' => [
                'code' => 500,
                'expected' => true,
            ]
        ];
    }
}
