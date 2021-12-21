<?php

namespace Unit\Application\InputData;

use App\Application\InputData\SubRedditThreadCommentRequestData;
use App\Entities\Reddit\Thread;
use Tests\TestCase;

class SubRedditThreadCommentRequestDataTest extends TestCase
{
    /**
     * getThread
     * @test
     *
     * @return void
     */
    public function getThread(): void
    {
        $thred = new Thread('title', 'text', 'url');

        $model = new SubRedditThreadCommentRequestData($thred);

        $this->assertEquals($model->getThread(), $thred);
    }

    /**
     * getUri
     * @test
     * @dataProvider getUriData
     *
     * @return void
     */
    public function getUri($url, $limit, $expected): void
    {
        $thred = new Thread('title', 'text', $url);

        $model = new SubRedditThreadCommentRequestData($thred);

        if (is_null($limit)) {
            $this->assertEquals($expected, $model->getUri());
        } else {
            $this->assertEquals($expected, $model->getUri($limit));
        }
    }

    public function getUriData(): array
    {
        return [
            'default limit case' => [
                'url' => 'https://www.reddit.com/r/test',
                'limit' => null,
                'expected' => 'https://www.reddit.com/r/test.json?limit=1000',
            ],
            'set limit case' => [
                'url' => 'https://www.reddit.com/r/test',
                'limit' => 500,
                'expected' => 'https://www.reddit.com/r/test.json?limit=500',
            ],
        ];
    }

    /**
     * getOptions
     * @test
     *
     * @return void
     */
    public function getOptions(): void
    {
        $thred = new Thread('title', 'text', 'url');
        $model = new SubRedditThreadCommentRequestData($thred);

        $this->assertEquals($model->getOptions(), [
            'headers' => [
                'Accept'        => 'application/json',
            ]
        ]);
    }

    /**
     * getMethod
     * @test
     *
     * @return void
     */
    public function getMethod(): void
    {
        $thred = new Thread('title', 'text', 'url');
        $model = new SubRedditThreadCommentRequestData($thred);

        $this->assertEquals($model->getMethod(), 'GET');
    }
}
