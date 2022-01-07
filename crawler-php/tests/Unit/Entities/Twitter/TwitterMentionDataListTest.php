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
     * getMentionList
     * @test
     * @dataProvider getMentionListDataProvider
     *
     * @param  mixed $data
     * @param  mixed $expected
     * @return void
     */
    public function getMentionList($data, $expected): void
    {
        $meta = [
            'result_count' => 10,
            'next_token' => 'page token',
        ];

        $entity = TwitterMentionDataList::getInstance($meta, $data);

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
            'normal case' => [
                'data' => $items,
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
    public function getMetaData($meta, $data, $expected): void
    {
        $entity = TwitterMentionDataList::getInstance($meta, $data);

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
            'normal case' => [
                'meta' => $expected,
                'data' => $items,
                'expected' => new TwitterMetaData($expected)
            ],
        ];
    }
}
