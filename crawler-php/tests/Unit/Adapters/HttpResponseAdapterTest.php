<?php

namespace Unit\Adapters;

use App\Adapters\HttpResponseAdapter;
use App\Application\OutputData\InnerApiResponse\HttpResponse;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use Tests\TestCase;
use \Mockery;

class HttpResponseAdapterTest extends TestCase
{
    /**
     * guzzleResponseToHttpResponse
     * @test
     *
     * @return void
     */
    public function guzzleResponseToHttpResponse(): void
    {
        $httpResponse = Mockery::mock(HttpResponse::class);
        $apiResponse = Mockery::mock(InnerApiResponse::class);
        $apiResponse->shouldReceive([
            'getStatusCode',
            'getBody->getContents',
        ]);


        $adapter = Mockery::mock('alias:' . HttpResponseAdapter::class);
        $adapter->shouldReceive('guzzleResponseToHttpResponse')->andReturn($httpResponse);

        $actual = HttpResponseAdapter::guzzleResponseToHttpResponse($apiResponse);

        $this->assertInstanceOf(HttpResponse::class, $actual);
    }
}
