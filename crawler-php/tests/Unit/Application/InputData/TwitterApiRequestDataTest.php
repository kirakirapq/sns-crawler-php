<?php

namespace Unit\Application\InputData;

use App\Application\InputData\TwitterApiRequestData;
use Tests\TestCase;

class TwitterApiRequestDataTest extends TestCase
{
    /**
     * getUri
     * @test
     * @dataProvider getUriData
     *
     * @return void
     */
    public function getUri($userId, $paginationToken, $expected): void
    {
        $model = new TwitterApiRequestData();

        $this->assertTrue(
            0 < strpos($model->getUri($userId, $paginationToken), $expected['userId'])
        );

        if (is_null($expected['paginationToken']) === true) {
            $this->assertFalse(
                strpos(
                    $model->getUri($userId, $paginationToken),
                    'pagination_token'
                )
            );
        } else {
            $this->assertTrue(
                0 < strpos(
                    $model->getUri($userId, $paginationToken),
                    $expected['paginationToken']
                )
            );
        }
    }

    public function getUriData(): array
    {
        return [
            'paginationToken is null case' => [
                'userId' => 'uerId',
                'paginationToken' => null,
                'expected' => [
                    'userId' => 'api.twitter.com/2/users/uerId/mentions?tweet.fields=created_at',
                    'paginationToken' => null
                ],
            ],
            'paginationToken is not null case' => [
                'userId' => 'userId-123',
                'paginationToken' => 'token',
                'expected' => [
                    'userId' => 'api.twitter.com/2/users/userId-123/mentions?tweet.fields=created_at',
                    'paginationToken' => 'pagination_token=token'
                ],
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

        $model = new TwitterApiRequestData();

        $expected =
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $model::BEARER_TOKEN,
                    'Accept'        => 'application/json',
                ]
            ];

        $this->assertEquals($expected, $model->getOptions());
    }

    /**
     * getMethod
     * @test
     *
     * @return void
     */
    public function getMethod(): void
    {
        $model = new TwitterApiRequestData();
        $this->assertEquals('GET', $model->getMethod());
    }
}
