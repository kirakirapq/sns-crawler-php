<?php

namespace Unit\Adapters;

use App\Adapters\RedditApiAdapter;
use App\Application\InputData\SubRedditRequestData;
use App\Application\InputData\SubRedditThreadCommentRequestData;
use App\Entities\Reddit\SubReddit;
use App\Entities\Reddit\Thread;
use App\Application\OutputData\InnerApiResponse\BigQueryResponse;
use Tests\TestCase;
use \Mockery;

class RedditApiAdapterTest extends TestCase
{
    /**
     * getSubRedditRequestData
     * @test
     *
     * @return void
     */
    public function getSubRedditRequestData(): void
    {
        $actual = RedditApiAdapter::getSubRedditRequestData();

        $this->assertInstanceOf(SubRedditRequestData::class, $actual);
    }

    /**
     * getCommentRequestData
     * @test
     *
     * @return void
     */
    public function getCommentRequestData(): void
    {
        $actual = RedditApiAdapter::getCommentRequestData(new Thread('title', 'text', 'url'));

        $this->assertInstanceOf(SubRedditThreadCommentRequestData::class, $actual);
    }

    /**
     * getSubReddit
     * @test
     *
     * @return void
     */
    public function getSubReddit(): void
    {
        $data['data']['children'] = [
            [
                'data' => [
                    'subreddit' => '',
                    'title' => '',
                    'selftext' => '',
                    'permalink' => ''
                ]
            ]
        ];

        $apiResponse = Mockery::mock(BigQueryResponse::class);
        $apiResponse->shouldReceive('getStatusCode')->andReturn(200)->once();
        $apiResponse->shouldReceive('hasError')->andReturn(false)->once();
        $apiResponse->shouldReceive('getBodyAsArray')->andReturn($data)->once();

        $actual = RedditApiAdapter::getSubReddit($apiResponse);

        $this->assertInstanceOf(SubReddit::class, $actual);
    }

    /**
     * getThread
     * @test
     *
     * @return void
     */
    public function getThread(): void
    {
        $subReddtRequestData = RedditApiAdapter::getCommentRequestData(new Thread('title', 'text', 'url'));

        $data[1]['data']['children'] = [
            [
                'data' => [
                    'subreddit' => '',
                    'title' => '',
                    'selftext' => '',
                    'permalink' => ''
                ],
            ]
        ];

        $apiResponse = Mockery::mock(BigQueryResponse::class);
        $apiResponse->shouldReceive('getStatusCode')->never();
        $apiResponse->shouldReceive('hasError')->never();
        $apiResponse->shouldReceive('getBodyAsArray')->andReturn($data)->once();

        $actual = RedditApiAdapter::getThread($apiResponse, $subReddtRequestData);

        $this->assertInstanceOf(Thread::class, $actual);
    }
}
