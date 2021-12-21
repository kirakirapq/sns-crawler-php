<?php

namespace Unit\Adapters;

use App\Adapters\BigQueryResponseAdapter;
use App\Application\OutputData\InnerApiResponse\BigQueryResponse;
use App\Entities\ResponseData\BigQuery\BigQueryData;
use Google\Cloud\BigQuery\QueryResults;
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
        $bigQueryResponse = Mockery::mock(BigQueryResponse::class);


        $bigQueryResponseAdapter = Mockery::mock('alias:' . BigQueryResponseAdapter::class);
        $bigQueryResponseAdapter->shouldReceive('getBigQueryResponse')->andReturn($bigQueryResponse);

        $actual = BigQueryResponseAdapter::getBigQueryResponse(201, $queryResult);

        $this->assertInstanceOf(BigQueryResponse::class, $actual);
    }

    /**
     * getBigqueryData
     * @test
     *
     * @return void
     */
    public function getBigqueryData(): void
    {
        $apiResponse = Mockery::mock(BigQueryResponse::class);
        $bigQueryData = Mockery::mock(BigQueryData::class);

        $bigQueryResponseAdapter = Mockery::mock('alias:' . BigQueryResponseAdapter::class);
        $bigQueryResponseAdapter->shouldReceive('getBigqueryData')->andReturn($bigQueryData);


        $actual = BigQueryResponseAdapter::getBigqueryData($apiResponse);

        $this->assertInstanceOf(BigQueryData::class, $actual);
    }
}
