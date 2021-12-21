<?php

namespace Unit\Entities\Reddit;

use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Entities\Reddit\SubReddit;
use App\Entities\Reddit\Thread;
use Illuminate\Support\Collection;
use Tests\TestCase;
use \Mockery;

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
    public function construct(bool $hasError, string $permalink): void
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

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $hasError,
                'getBodyAsArray' => $bodyArray,
            ])
            ->once();

        if ($hasError === true) {
            $apiResponse->shouldReceive(['getBody' => ''])->once();
        } else {
            $apiResponse->shouldReceive(['getBody' => ''])->never();
        }

        $model = new SubReddit($apiResponse->getMock());

        $this->assertInstanceOf(SubReddit::class, $model);
    }

    public function getUrlDataProvider(): array
    {
        return [
            'has error is true case' => [
                'hasError' => true,
                'permalink' => '/test',
            ],
            'has error is false case' => [
                'hasError' => false,
                'permalink' => '/test',
            ],
            'thread is true case' => [
                'hasError' => false,
                'permalink' => '/test',
            ],
            'thread is false case' => [
                'hasError' => true,
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

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => false,
                'getBodyAsArray' => $bodyArray,
            ])
            ->once();
        $apiResponse->shouldReceive(['getBody' => ''])->never();

        $permalink = '/test/test';
        $model = new SubReddit($apiResponse->getMock());

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

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => false,
                'getBodyAsArray' => $bodyArray,
            ])
            ->once();
        $apiResponse->shouldReceive(['getBody' => ''])->never();

        $model = new SubReddit($apiResponse->getMock());

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
     * getStatusCode
     * @test
     * @dataProvider getStatusCodeDataProvider
     *
     * @param  mixed $code
     * @param  mixed $expected
     * @return void
     */
    public function getStatusCode(int $code, int $expected): void
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

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => $code,
                'hasError' => false,
                'getBodyAsArray' => $bodyArray,
            ])
            ->once();
        $apiResponse->shouldReceive(['getBody' => ''])->never();

        $model = new SubReddit($apiResponse->getMock());

        $this->assertEquals($expected, $model->getStatusCode());
    }

    public function getStatusCodeDataProvider(): array
    {
        return [
            '200 case' => [
                'code' => 200,
                'expected' => 200,
            ],
            '404 case' => [
                'code' => 404,
                'expected' => 404,
            ]
        ];
    }

    /**
     * hasError
     * @test
     * @dataProvider hasErrorDataProvider
     *
     * @param  mixed $code
     * @param  mixed $hasError
     * @param  mixed $expected
     * @return void
     */
    public function hasError(int $code, bool $hasError, bool $expected): void
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

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => $code,
                'hasError' => $hasError,
                'getBodyAsArray' => $bodyArray,
            ])
            ->once();
        if ($hasError === true) {
            $apiResponse->shouldReceive(['getBody' => ''])->once();
        } else {
            $apiResponse->shouldReceive(['getBody' => ''])->never();
        }

        $model = new SubReddit($apiResponse->getMock());

        $this->assertEquals($expected, $model->hasError());
    }

    public function hasErrorDataProvider(): array
    {
        return [
            'has error is false case' => [
                'code' => 200,
                'hasError' => false,
                'expected' => false,
            ],
            'has error is true case' => [
                'code' => 404,
                'hasError' => true,
                'expected' => true,
            ]
        ];
    }

    /**
     * getErrorMessage
     * @test
     * @dataProvider getErrorMessageDataProvider
     *
     * @param  mixed $code
     * @param  mixed $hasError
     * @param  mixed $errorMessage
     * @param  mixed $expected
     * @return void
     */
    public function getErrorMessage(int $code, bool $hasError, string $errorMessage, ?string $expected): void
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

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => $code,
                'hasError' => $hasError,
                'getBodyAsArray' => $bodyArray,
            ])
            ->once();
        if ($hasError === true) {
            $apiResponse->shouldReceive(['getBody' => $errorMessage])->once();
        } else {
            $apiResponse->shouldReceive(['getBody' => $errorMessage])->never();
        }

        $model = new SubReddit($apiResponse->getMock());

        $this->assertEquals($expected, $model->getErrorMessage());
    }

    public function getErrorMessageDataProvider(): array
    {
        return [
            'has error is false case' => [
                'code' => 200,
                'hasError' => false,
                'errorMessage' => 'error message',
                'expected' => null,
            ],
            'has error is true case' => [
                'code' => 404,
                'hasError' => true,
                'errorMessage' => 'error message',
                'expected' => 'error message',
            ]
        ];
    }

    /**
     * isThread
     * @test
     * @dataProvider isThreadDataProvider
     *
     * @param  mixed $code
     * @param  mixed $hasError
     * @param  mixed $url
     * @param  mixed $expected
     * @return void
     */
    public function isThread(int $code, bool $hasError, string $url, bool $expected): void
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

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => $code,
                'hasError' => $hasError,
                'getBodyAsArray' => $bodyArray,
            ])
            ->once();
        if ($hasError === true) {
            $apiResponse->shouldReceive(['getBody' => ''])->once();
        } else {
            $apiResponse->shouldReceive(['getBody' => ''])->never();
        }

        $model = new SubReddit($apiResponse->getMock());
        $actual = $model->isThread($model->getUrl($url));

        $this->assertEquals($expected, $actual);
    }

    public function isThreadDataProvider(): array
    {
        return [
            'thread is false case' => [
                'code' => 200,
                'hasError' => false,
                'url' => 'http://hoge.fuga/test',
                'expected' => false,
            ],
            'thread is true case' => [
                'code' => 200,
                'hasError' => false,
                'url' => '/test',
                'expected' => true,
            ]
        ];
    }
}
