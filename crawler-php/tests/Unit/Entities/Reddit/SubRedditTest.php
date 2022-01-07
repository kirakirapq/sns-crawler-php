<?php

namespace Unit\Entities\Reddit;

use App\Entities\Reddit\SubReddit;
use App\Entities\Reddit\Thread;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SubRedditTest extends TestCase
{
    /**
     * construct
     * @test
     * @dataProvider getUrlDataProvider
     *
     * @param  mixed $hasError
     * @param  mixed $permalink
     * @return void
     */
    public function construct(string $permalink): void
    {
        $bodyArray['data']['children'] = [
            [
                'data' => [
                    'subreddit' => '',
                    'title' => '',
                    'selftext' => '',
                    'permalink' => $permalink,
                ],
            ]
        ];
        $model = new SubReddit($bodyArray);

        $this->assertInstanceOf(SubReddit::class, $model);
    }

    public function getUrlDataProvider(): array
    {
        return [
            'has error is true case' => [
                'permalink' => '/test',
            ],
            'has error is false case' => [
                'permalink' => '/test',
            ],
            'thread is true case' => [
                'permalink' => '/test',
            ],
            'thread is false case' => [
                'permalink' => 'http://hoge.fuga/test',
            ],
        ];
    }

    /**
     * getUrl
     * @test
     *
     * @return void
     */
    public function getUrl(): void
    {
        $bodyArray['data']['children'] = [
            [
                'data' => [
                    'subreddit' => '',
                    'title' => '',
                    'selftext' => '',
                    'permalink' => '/test',
                ],
            ]
        ];
        $permalink = '/test/test';
        $model = new SubReddit($bodyArray);

        $this->assertEquals(sprintf('https://%s%s', $model::HOST_NAME, $permalink), $model->getUrl($permalink));
    }

    /**
     * getThreadList
     * @test
     * @dataProvider getThreadListDataProvider
     *
     * @param  mixed $bodyDataArray
     * @param  mixed $expected
     * @return void
     */
    public function getThreadList(array $bodyDataArray, ?Collection $expected): void
    {
        $bodyArray['data']['children'] = [
            [
                'data' => $bodyDataArray,
            ]
        ];
        $model = new SubReddit($bodyArray);

        $this->assertEquals($expected, $model->getThreadList());
    }

    public function getThreadListDataProvider(): array
    {
        $title = 'title';
        $text = 'text';
        $permalink = '/test';
        $url = sprintf('https://%s%s', 'www.reddit.com', $permalink);

        return [
            'thread is true case' => [
                'bodyDataArray' => [
                    'subreddit' => '',
                    'title' => $title,
                    'selftext' => $text,
                    'permalink' => $permalink,
                ],
                'expected' => collect([new Thread($title, $text, $url)])
            ],
            'thread is false case' => [
                'bodyDataArray' => [
                    'subreddit' => '',
                    'title' => $title,
                    'selftext' => $text,
                    'permalink' => 'http://hoge.fuga/test',
                ],
                'expected' => collect([]),
            ]
        ];
    }

    /**
     * isThread
     * @test
     * @dataProvider isThreadDataProvider
     *
     * @param  mixed $url
     * @param  mixed $expected
     * @return void
     */
    public function isThread(string $url, bool $expected): void
    {
        $bodyArray['data']['children'] = [
            [
                'data' => [
                    'subreddit' => '',
                    'title' => '',
                    'selftext' => '',
                    'permalink' => $url,
                ],
            ]
        ];

        $model = new SubReddit($bodyArray);
        $actual = $model->isThread($model->getUrl($url));

        $this->assertEquals($expected, $actual);
    }

    public function isThreadDataProvider(): array
    {
        return [
            'thread is false case' => [
                'url' => 'http://hoge.fuga/test',
                'expected' => false,
            ],
            'thread is true case' => [
                'url' => '/test',
                'expected' => true,
            ]
        ];
    }
}
