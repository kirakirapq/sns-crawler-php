<?php

namespace Unit\Adapters;

use App\Adapters\TwitterApiAdapter;
use App\Application\OutputData\InnerApiResponse\HttpResponse;
use App\Entities\Twitter\TwitterMentionDataList;
use Tests\TestCase;
use \Mockery;

class TwitterApiAdapterTest extends TestCase
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
        $response = Mockery::mock('alias:' . TwitterMentionDataList::class);
        $response->shouldReceive('getInstance')->andReturn($response);

        $adapter = Mockery::mock('alias:' . HttpResponseAdapter::class);
        $adapter->shouldReceive('responseToMentionDataList')->andReturn($response);

        $actual = TwitterApiAdapter::responseToMentionDataList($httpResponse);

        $this->assertInstanceOf(TwitterMentionDataList::class, $actual);
    }
}
