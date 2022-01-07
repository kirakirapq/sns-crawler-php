<?php

namespace Unit\Adapters;

use App\Adapters\TwitterApiAdapter;
use App\Entities\Twitter\TwitterMentionDataList;
use Tests\TestCase;

class TwitterApiAdapterTest extends TestCase
{
    /**
     * guzzleResponseToHttpResponse
     * @test
     *
     * @return void
     */
    public function responseToMentionDataList(): void
    {
        $httpResponse = [
            'meta' => [],
            'data' => [
                ['created_at' => '2021-01-01']
            ]
        ];

        $actual = TwitterApiAdapter::responseToMentionDataList($httpResponse);

        $this->assertInstanceOf(TwitterMentionDataList::class, $actual);
    }
}
