<?php

namespace Unit\Adapters;

use App\Adapters\CsvResponseAdapter;
use App\Application\OutputData\InnerApiResponse\CsvResponse;
use Tests\TestCase;
use \Mockery;

class CsvResponseAdapterTest extends TestCase
{
    /**
     * getCsvResponse
     * @test
     *
     * @return void
     */
    public function getCsvResponse(): void
    {
        $response = Mockery::mock(CsvResponse::class);

        $adapter = Mockery::mock('alias:' . CsvResponseAdapter::class);
        $adapter->shouldReceive('getCsvResponse')->andReturn($response);

        $actual = CsvResponseAdapter::getCsvResponse(200, null);

        $this->assertInstanceOf(CsvResponse::class, $actual);
    }
}
