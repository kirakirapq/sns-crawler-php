<?php

namespace Unit\Adapters;

use App\Adapters\BigQueryResponseAdapter;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Entities\ResponseData\BigQuery\BigQueryData;
use App\Entities\BigQuery\Colmun;
use App\Entities\BigQuery\LatestData;
use Google\Cloud\BigQuery\QueryResults;
use \ArrayIterator;
use \StdClass;
use Tests\TestCase;
use \Mockery;

class BigQueryResponseAdapterTest extends TestCase
{
    /**
     * getBigQueryResponse
     * @test
     *
     * @return void
     */
    public function getBigQueryResponse(): void
    {
        $queryResult = Mockery::mock(QueryResults::class);
        $actual = BigQueryResponseAdapter::getBigQueryResponse(201, $queryResult);

        $this->assertInstanceOf(InnerApiResponse::class, $actual);
    }

    /**
     * getBigqueryData
     * @test
     *
     * @return void
     */
    public function getBigqueryData(): void
    {
        $identity = [
            'projectId' => '',
            'jobId' => '',
            'location' => ''
        ];
        $rows = new ArrayIterator();
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


        $actual = BigQueryResponseAdapter::getBigqueryData($apiResponse);

        $this->assertInstanceOf(BigQueryData::class, $actual);
    }

    public function getLatestData(): void
    {
        $data = [
            [
                ['f1' => 'v1'],
                ['f2' => 'v2'],
                ['f2' => 'v3'],
            ],
            [
                ['f3' => 'v3'],
                ['f4' => 'v4'],
                ['f5' => 'v5'],
            ]
        ];

        $colmuns = [];
        foreach ($data[0] as $f => $v) {
            $colmuns[$f] = new Colmun($f, $v);
        }
        $expected = new LatestData('table', $colmuns);

        $actual = BigQueryResponseAdapter::getLatestData('table', collect($data));

        $this->assertEquals($expected, $actual);
    }
}
