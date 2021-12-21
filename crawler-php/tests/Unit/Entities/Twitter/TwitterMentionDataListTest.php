<?php

namespace Unit\Entities\Twitter;

use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Entities\Twitter\TwitterMentionDataList;
use App\Entities\Twitter\TwitterMetaData;
use Carbon\Carbon;
use Tests\TestCase;
use \Mockery;

/**
 * TwitterMentionDataListTest
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TwitterMentionDataListTest extends TestCase
{
    /**
     * getStatusCode
     * @test
     *
     * @return void
     */
    public function getStatusCode(): void
    {
        $resltData = [
            'meta' => [
                'result_count' => 10,
                'next_token' => 'page token',
            ],
            'data' => [],
        ];

        $expected = 200;
        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => $expected,
                'hasError' => false,
                'getBodyAsArray' => $resltData,
            ])
            ->getMock();

        $entity = TwitterMentionDataList::getInstance($apiResponse);

        $this->assertEquals($expected, $entity->getStatusCode());
    }

    /**
     * hasError
     * @test
     *
     * @return void
     */
    public function hasError(): void
    {
        $resltData = [
            'meta' => [
                'result_count' => 10,
                'next_token' => 'page token',
            ],
            'data' => [],
        ];

        $expected = false;
        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 400,
                'hasError' => $expected,
                'getBodyAsArray' => $resltData,
            ])
            ->getMock();

        $entity = TwitterMentionDataList::getInstance($apiResponse);

        $this->assertEquals($expected, $entity->hasError());
    }

    /**
     * getMentionList
     * @test
     * @dataProvider getMentionListDataProvider
     *
     * @param  mixed $data
     * @param  mixed $hasError
     * @param  mixed $expected
     * @return void
     */
    public function getMentionList($data, $hasError, $expected): void
    {
        $resltData = [
            'meta' => [
                'result_count' => 10,
                'next_token' => 'page token',
            ],
            'data' => $data,
        ];

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $hasError,
                'getBodyAsArray' => $resltData,
                'getBody' => ''
            ])
            ->getMock();

        $entity = TwitterMentionDataList::getInstance($apiResponse);

        $this->assertEquals($expected, $entity->getMentionList());
    }

    public function getMentionListDataProvider(): array
    {
        $items = [
            ['created_at' => '2021-01-01 00:00:00'],
            ['created_at' => '2021-01-02 00:00:00'],
            ['created_at' => '2021-01-03 00:00:00'],
        ];
        $expected = [
            ['created_at' => '2021-01-01 00:00:00', 'date' => '2021-01-01'],
            ['created_at' => '2021-01-02 00:00:00', 'date' => '2021-01-02'],
            ['created_at' => '2021-01-03 00:00:00', 'date' => '2021-01-03'],
        ];
        return [
            'error case' => [
                'data' => $items,
                'hasError' => true,
                'expected' => null
            ],
            'normal case' => [
                'data' => $items,
                'hasError' => false,
                'expected' => collect($expected)
            ],
        ];
    }


    /**
     * getMetaData
     * @test
     * @dataProvider getMetaDataProvider
     *
     * @param  mixed $meta
     * @param  mixed $data
     * @param  mixed $hasError
     * @param  mixed $expected
     * @return void
     */
    public function getMetaData($meta, $data, $hasError, $expected): void
    {
        $resltData = [
            'meta' => $meta,
            'data' => $data,
        ];

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $hasError,
                'getBodyAsArray' => $resltData,
                'getBody' => ''
            ])
            ->getMock();

        $entity = TwitterMentionDataList::getInstance($apiResponse);

        $this->assertEquals($expected, $entity->getMetaData());
    }

    public function getMetaDataProvider(): array
    {
        $items = [
            ['created_at' => '2021-01-01 00:00:00'],
            ['created_at' => '2021-01-02 00:00:00'],
            ['created_at' => '2021-01-03 00:00:00'],
        ];
        $expected = [
            'key' => 'val'
        ];
        return [
            'error case' => [
                'meta' => null,
                'data' => $items,
                'hasError' => true,
                'expected' => null
            ],
            'normal case' => [
                'meta' => $expected,
                'data' => $items,
                'hasError' => false,
                'expected' => new TwitterMetaData($expected)
            ],
        ];
    }


    /**
     * getErrorMessage
     * @test
     * @dataProvider getErrorMessageDataProvider
     *
     * @param  mixed $data
     * @param  mixed $hasError
     * @param  mixed $message
     * @param  mixed $expected
     * @return void
     */
    public function getErrorMessage($data, $hasError, $message, $expected): void
    {
        $resltData = [
            'meta' => [
                'result_count' => 10,
                'next_token' => 'page token',
            ],
            'data' => $data,
        ];

        $apiResponse = Mockery::mock(InnerApiResponse::class)
            ->shouldReceive([
                'getStatusCode' => 200,
                'hasError' => $hasError,
                'getBodyAsArray' => $resltData,
                'getBody' => $message,
            ])
            ->getMock();

        $entity = TwitterMentionDataList::getInstance($apiResponse);

        $this->assertEquals($expected, $entity->getErrorMessage());
    }

    public function getErrorMessageDataProvider(): array
    {
        $items = [
            ['created_at' => '2021-01-01 00:00:00'],
            ['created_at' => '2021-01-02 00:00:00'],
            ['created_at' => '2021-01-03 00:00:00'],
        ];
        return [
            'error case' => [
                'data' => $items,
                'hasError' => true,
                'message' => 'error message,',
                'expected' => 'error message,'
            ],
            'normal case' => [
                'data' => $items,
                'hasError' => false,
                'message' => '',
                'expected' => null
            ],
        ];
    }
}
