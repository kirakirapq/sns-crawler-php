<?php

namespace Unit\Entities\ResponseData\BigQuery;

use App\Entities\ResponseData\Bigquery\BigQueryData;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use \ArrayIterator;
use Tests\TestCase;
use \Mockery;

class BigQueryDataTest extends TestCase
{
    /**
     * setData
     * @test
     * @dataProvider setDataProvider
     *
     * @param  mixed $rawData
     * @param  mixed $identity
     * @param  mixed $expected
     * @return void
     */
    public function setData($rawData, $identity, $expected): void
    {
        if (is_null($rawData) === true) {
            $rows = new ArrayIterator();
        } else {
            $rows = new ArrayIterator($rawData);
        }

        $body = Mockery::mock(StdClass::class)
            ->shouldReceive([
                'identity' => $identity,
                'rows' => $rows,
                'info' => null,
            ])
            ->getMock();

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => false,
                'getBody' => $body,
            ])
            ->getMock();

        $bigQueryData = new BigQueryData($apiResponse);

        $this->assertEquals($expected, $bigQueryData->getDataList());
    }

    public function setDataProvider(): array
    {
        return [
            'rawData null case' => [
                'rawData' => null,
                'identity' => [
                    'projectId' => '',
                    'jobId' => '',
                    'location' => ''
                ],
                'expected' => collect([])
            ],
            'rawData not case' => [
                'rawData' => [1, 2, 3],
                'identity' => [
                    'projectId' => '',
                    'jobId' => '',
                    'location' => ''
                ],
                'expected' => collect([1, 2, 3])
            ],
        ];
    }

    /**
     * getStatusCode
     * @test
     *
     * @return void
     */
    public function getStatusCode(): void
    {
        $expected = 200;
        $rows = new ArrayIterator();

        $body = Mockery::mock(StdClass::class)
            ->shouldReceive([
                'identity' => null,
                'rows' => $rows,
                'info' => null,
            ])
            ->getMock();

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => $expected,
                'hasError' => false,
                'getBody' => $body,
            ])
            ->getMock();

        $bigQueryData = new BigQueryData($apiResponse);

        $this->assertEquals($expected, $bigQueryData->getStatusCode());
    }

    /**
     * getDataList
     * @test
     *
     * @return void
     */
    public function getDataList(): void
    {
        $data = [1, 2, 3];
        $expected = collect($data);
        $rows = new ArrayIterator($data);

        $body = Mockery::mock(StdClass::class)
            ->shouldReceive([
                'identity' => null,
                'rows' => $rows,
                'info' => null,
            ])
            ->getMock();

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => false,
                'getBody' => $body,
            ])
            ->getMock();

        $bigQueryData = new BigQueryData($apiResponse);

        $this->assertEquals($expected, $bigQueryData->getDataList());
    }

    /**
     * getErrorMessage
     * @test
     * @dataProvider getErrorMessageDataProvider
     *
     * @param  mixed $hasError
     * @param  mixed $info
     * @return void
     */
    public function getErrorMessage($hasError, $info, $expected): void
    {
        $data = [1, 2, 3];
        $rows = new ArrayIterator($data);

        $body = Mockery::mock(StdClass::class)
            ->shouldReceive([
                'identity' => null,
                'rows' => $rows,
                'info' => $info,
            ])
            ->getMock();

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $hasError,
                'getBody' => $body,
            ])
            ->getMock();

        $bigQueryData = new BigQueryData($apiResponse);

        $this->assertEquals($expected, $bigQueryData->getErrorMessage());
    }

    public function getErrorMessageDataProvider(): array
    {
        return [
            'error case' => [
                'hasError' => true,
                'info' => 'message',
                'expected' => 'message',
            ],
            'no error case' => [
                'hasError' => false,
                'info' => '',
                'expected' => null,
            ]
        ];
    }

    /**
     * hasError
     * @test
     * @dataProvider hasErrorDataProvide
     *
     * @param  mixed $hasError
     * @param  mixed $expected
     * @return void
     */
    public function hasError($hasError, $expected): void
    {
        $data = [1, 2, 3];
        $rows = new ArrayIterator($data);

        $body = Mockery::mock(StdClass::class)
            ->shouldReceive([
                'identity' => null,
                'rows' => $rows,
                'info' => '',
            ])
            ->getMock();

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $hasError,
                'getBody' => $body,
            ])
            ->getMock();

        $bigQueryData = new BigQueryData($apiResponse);

        $this->assertEquals($expected, $bigQueryData->hasError());
    }

    public function hasErrorDataProvide(): array
    {
        return [
            'error case' => [
                'hasError' => true,
                'expected' => true,
            ],
            'no error case' => [
                'hasError' => false,
                'expected' => false,
            ],
        ];
    }
}
